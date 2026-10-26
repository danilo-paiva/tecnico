# -*- coding: utf-8 -*-
"""
Ponto de entrada da API Flask.

Execute com: python app.py
"""
import sys

from server import Server


def main():
    # Garante que os prints apareçam imediatamente no terminal.
    try:
        sys.stdout.reconfigure(line_buffering=True)
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
