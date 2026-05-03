# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview
System for condominium management focusing on database schema and query reporting.

## Architecture
- **Database**: MySQL/MariaDB based.
- **Schema**: 
  - Core entities: `unidades`, `moradores`, `funcionarios`, `locais`.
  - Relations: `morador_unidade` (junction for residency history), `ocorrencias` (incidents), `reservas` (common area booking), `cobrancas` (financials).
- **Query Patterns**: Business logic is implemented via SQL queries in `consultas.sql` focusing on auditing, financial reporting, and occupancy tracking.

## Common Commands
- **Database Setup**: Run `tabelas.sql` to initialize the `sistemaCondominio` database.
- **Data Testing**: Use `exemplos.sql` for seeding test data.
- **Report Verification**: Refer to `consultas.sql` for existing business logic queries (D1-D10).
