# -*- coding: utf-8 -*-
"""
Ponto de entrada da API Flask (camada de inicialização).

Quem chama: o terminal, com "python app.py".
O que faz: cria o Server, conecta ao MySQL, registra rotas e sobe o Flask.
Não tem rota, regra de negócio nem SQL — isso fica em api/.
"""
import sys

from server import Server


def main():
    """Sobe o servidor e trata o Ctrl+C; relança qualquer erro de inicialização."""
    # Garante que os prints apareçam imediatamente no terminal.
    try:
        sys.stdout.reconfigure(line_buffering=True)
    # reconfigure() não existe em Python antigo: nesse caso o print continua normal.
    except AttributeError:
        pass

    try:
        servidor = Server()
        servidor.init()
        servidor.run()
    except KeyboardInterrupt:
        print("\nServidor encerrado.")
    except Exception as erro:
        print(f"[ERRO] Falha ao iniciar o servidor: {erro}")
        raise


if __name__ == "__main__":
    main()
