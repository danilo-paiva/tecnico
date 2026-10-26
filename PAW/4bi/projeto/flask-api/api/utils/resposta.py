# -*- coding: utf-8 -*-
"""Envelope único de resposta: { success, message, data }."""
from flask import jsonify


def responder(status: int, mensagem: str, dados=None):
    return jsonify({
        "success": status < 400,
        "message": mensagem,
        "data": dados if dados is not None else {},
    }), status
