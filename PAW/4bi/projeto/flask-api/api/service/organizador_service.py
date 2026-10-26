# -*- coding: utf-8 -*-
import bcrypt

from api.model.organizador import Organizador
from api.utils.error_response import ErrorResponse


class OrganizadorService:
    """Service de Organizador.

    Regras de negócio: e-mail único, hash da senha, login/JWT,
    permissões de perfil e exclusão segura (sem eventos vinculados).
    """

    def __init__(self, organizador_dao_dependency, evento_dao_dependency, token_jwt_dependency):
        self.__organizador_dao = organizador_dao_dependency
        self.__evento_dao = evento_dao_dependency
        self.__token_jwt = token_jwt_dependency

    def create_organizador(self, dados: dict, perfil: str = None) -> dict:
        organizador = Organizador()
        organizador.nome = dados.get("nome")
        organizador.email = dados.get("email")
        organizador.senha = dados.get("senha")
        organizador.telefone = dados.get("telefone")
        organizador.perfil = perfil or dados.get("perfil") or "comum"

        if self.__organizador_dao.existe_email(organizador.email):
            raise ErrorResponse(
                409,
                "E-mail já cadastrado",
                {"campo": "email", "mensagem": f"O e-mail {organizador.email} já está em uso."},
            )

        senha_hash = bcrypt.hashpw(organizador.senha.encode("utf-8"), bcrypt.gensalt(10)).decode("utf-8")
        id_organizador = self.__organizador_dao.create(organizador, senha_hash)
        return self.__organizador_dao.find_by_id(id_organizador)

    def create_organizador_como_administrador(self, dados: dict, usuario_logado: dict) -> dict:
        """Cadastro pela rota administrativa: apenas administrador define o perfil."""
        if usuario_logado.get("perfil") != "administrador":
            raise ErrorResponse(
                403,
                "Acesso negado",
                {"mensagem": "Somente administradores podem cadastrar organizadores."},
            )
        return self.create_organizador(dados, dados.get("perfil") or "comum")

    def find_all(self, filtros: dict = None) -> list[dict]:
        return self.__organizador_dao.find_all(filtros or {})

    def find_by_id(self, id_organizador: int) -> dict:
        organizador = self.__organizador_dao.find_by_id(id_organizador)
        if not organizador:
            raise ErrorResponse(
                404,
                "Organizador não encontrado",
                {"mensagem": f"Não existe organizador com o id {id_organizador}."},
            )
        return organizador

    def update_organizador(self, id_organizador: int, dados: dict, usuario_logado: dict) -> dict:
        eh_administrador = usuario_logado.get("perfil") == "administrador"
        if not eh_administrador and int(usuario_logado.get("idOrganizador")) != int(id_organizador):
            raise ErrorResponse(
                403,
                "Acesso negado",
                {"mensagem": "Você só pode editar o seu próprio cadastro."},
            )

        atual = self.__organizador_dao.find_by_id(id_organizador)
        if not atual:
            raise ErrorResponse(
                404,
                "Organizador não encontrado",
                {"mensagem": f"Não existe organizador com o id {id_organizador}."},
            )

        organizador = Organizador()
        organizador.idOrganizador = id_organizador
        organizador.nome = dados.get("nome", atual["nome"])
        organizador.email = dados.get("email", atual["email"])
        organizador.telefone = dados.get("telefone", atual["telefone"])

        perfil_desejado = dados.get("perfil", atual["perfil"])
        organizador.perfil = perfil_desejado if eh_administrador else atual["perfil"]

        senha_hash = None
        if dados.get("senha"):
            organizador.senha = dados.get("senha")
            senha_hash = bcrypt.hashpw(
                organizador.senha.encode("utf-8"), bcrypt.gensalt(10)
            ).decode("utf-8")

        if self.__organizador_dao.existe_email(organizador.email, organizador.idOrganizador):
            raise ErrorResponse(
                409,
                "E-mail já cadastrado",
                {"campo": "email", "mensagem": f"O e-mail {organizador.email} já está em uso."},
            )

        if not self.__organizador_dao.update(organizador, senha_hash):
            raise ErrorResponse(500, "Falha ao atualizar organizador")

        return self.__organizador_dao.find_by_id(id_organizador)

    def delete_organizador(self, id_organizador: int, usuario_logado: dict) -> bool:
        if usuario_logado.get("perfil") != "administrador":
            raise ErrorResponse(
                403,
                "Acesso negado",
                {"mensagem": "Somente administradores podem excluir organizadores."},
            )

        if int(usuario_logado.get("idOrganizador")) == int(id_organizador):
            raise ErrorResponse(
                400,
                "Operação não permitida",
                {"mensagem": "Não é permitido excluir o próprio usuário logado."},
            )

        organizador = self.__organizador_dao.find_by_id(id_organizador)
        if not organizador:
            raise ErrorResponse(
                404,
                "Organizador não encontrado",
                {"mensagem": f"Não existe organizador com o id {id_organizador}."},
            )

        total_eventos = self.__evento_dao.count_by_organizador(id_organizador)
        if total_eventos > 0:
            raise ErrorResponse(
                409,
                "Organizador possui eventos vinculados",
                {
                    "mensagem": (
                        f"Exclua ou transfira os {total_eventos} evento(s) "
                        "antes de remover este organizador."
                    )
                },
            )

        return self.__organizador_dao.delete(id_organizador)

    def login(self, email_login, senha):
        if not email_login or not senha:
            raise ErrorResponse(
                400,
                "Dados de login incompletos",
                {"mensagem": "Informe e-mail e senha."},
            )

        credenciais = self.__organizador_dao.buscar_credenciais(
            str(email_login).strip().lower()
        )

        if not credenciais:
            raise ErrorResponse(
                401, "Credenciais inválidas", {"mensagem": "E-mail ou senha incorretos."}
            )

        confere = bcrypt.checkpw(
            str(senha).encode("utf-8"), credenciais["senha"].encode("utf-8")
        )
        if not confere:
            raise ErrorResponse(
                401, "Credenciais inválidas", {"mensagem": "E-mail ou senha incorretos."}
            )

        organizador = {chave: valor for chave, valor in credenciais.items() if chave != "senha"}
        token = self.__token_jwt.gerar_token(organizador)
        return organizador, token
