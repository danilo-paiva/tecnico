use sistemaCondominio;

# D1. Listar todas as unidades cadastradas e seu status (ocupada/vazia, se aplicavel).
select id_unidade as "unidade", numero_apartamento as "numero", status_unidade as "status" 
from unidades;

# D2. Exibir o historico de moradores de uma unidade informada (por identificador da unidade).
select id_morador_unidade, 
       (select nome_morador from moradores where moradores.id_morador = morador_unidade.id_morador) as "nome_morador", 
       vinculo_morador, 
       data_inicio, 
       data_fim 
from morador_unidade 
where id_unidade = 1 
order by data_inicio desc;

# D3. Listar moradores ativos no condominio em ordem alfabetica.
select id_morador, nome_morador, cpf_morador 
from moradores 
where id_morador in (
	select id_morador 
    from morador_unidade 
    where data_fim is null
) 
order by nome_morador asc;

# D4. Listar reservas de uma area comum em uma data informada.
select * 
from reservas 
where id_local = 1 
	and inicio_reserva >= '2026-04-20 00:00:00' 
	and inicio_reserva <= '2026-04-20 23:59:59';

# D5. Verificar conflitos: reservas duplicadas para a mesma area, data e horario (auditoria).
select id_local, inicio_reserva, fim_reserva, count(*) as "quantidade_reservas" 
from reservas 
group by id_local, inicio_reserva, fim_reserva 
having count(*) > 1;

# D6. Exibir ocorrencias registradas em um intervalo de datas.
select id_ocorrencia, id_unidade, motivo_ocorrencia, data_ocorrencia 
from ocorrencias 
where data_ocorrencia >= '2026-04-01 00:00:00' 
	and data_ocorrencia <= '2026-04-30 23:59:59';

# D7. Exibir total arrecadado em um mes (pagamentos confirmados no periodo).
select sum(valor) as "total_arrecadado" 
from cobrancas 
where data_pagamento >= '2026-04-01'
	and data_pagamento <= '2026-04-30';

# D8. Listar unidades inadimplentes no mes (sem pagamento da cobranca do periodo).
select id_unidade, valor, data_vencimento 
from cobrancas 
where data_vencimento >= '2026-04-01' 
	and data_vencimento <= '2026-04-30' 
	and data_pagamento is null;

# D9. Exibir as 3 unidades com maior numero de ocorrencias no trimestre.
select id_unidade, count(id_ocorrencia) as "total_ocorrencias" 
from ocorrencias 
where data_ocorrencia >= '2026-01-01 00:00:00' 
  and data_ocorrencia <= '2026-03-31 23:59:59' 
group by id_unidade 
order by total_ocorrencias desc 
limit 3;

# D10. Listar unidades que tiveram mudanca de responsavel no semestre (com base no historico).
select distinct id_unidade 
from morador_unidade 
where morador_responsavel = true 
  and data_inicio >= '2026-01-01' 
  and data_inicio <= '2026-06-30';