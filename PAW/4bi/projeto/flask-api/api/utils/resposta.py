# -*- coding: utf-8 -*-
"""
Envelope único de resposta: { success, message, data } (camada de utilidades).

Quem usa: todos os Controls e os Middlewares, para não montar jsonify na mão.
Devolve: a tupla (resposta JSON, status) pronta para o Flask enviar.
"""
from flask import jsonify


def responder(status: int, mensagem: str, dados=None):
    """Recebe o status HTTP, a mensagem e os dados; devolve a resposta no envelope padrão."""
    return jsonify({
        # success é derivado do status: 2xx = True; 4xx/5xx = False.
        "success": status < 400,
        "message": mensagem,
        "data": dados if dados is not None else {},
    }), status
