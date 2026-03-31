create database sistemaCondominio;

use sistemaCondominio;

#pd usar?
#autoincrement
# tem que colocar o funcionario na ocorrencia?

create table unidades(
id_unidade int primary key,
id_responsavel int not null,
status_unidade varchar(255) not null,
foreign key (id_responsavel) references moradores (id_morador)
);

create table moradores(
id_morador int primary key,
nome_morador varchar(255) not null,
id_unidade int not null,
tipo varchar(255) not null,
ativo boolean,
foreign key (id_unidade) references unidades (id_unidade)
);

create table funcionarios(
id_funcionario int primary key,
nome_funcionario varchar(255) not null,
funcao_funcionario varchar(255) not null
);

create table ocorrencias(
id_ocorrencia int primary key,
id_unidade int not null,
motivo_ocorrencia varchar(255) not null,
data_ocorrencia datetime,
foreign key(id_unidade) references unidades (id_unidade)
);

create table locais(
id_local int primary key,
nome_local varchar(255) not null
);

create table reservas(
id_reserva int primary key,
id_unidade int not null,
id_local int not null,
inicio_reserva datetime not null,
fim_reserva datetime not null,
foreign key (id_unidade) references unidade (id_unidade),
foreign key (id_local) references locais (id_local)
);

create table pagamentos(
id_pagamento int primary key,
id_unidade int not null,
status_pagamento varchar(255) not null,
valor float not null,
foreign key (id_unidade) references unidades (id_unidade)
);
#D1. Listar todas as unidades cadastradas e seu status (ocupada/vazia, se aplicavel).
select id_uniadade, status_unidade from unidades;

# D2. Exibir o historico de moradores de uma unidade informada (por identificador da unidade).
select * from moradores
where id_unidade = 10;

# D3. Listar moradores ativos no condom ́ınio em ordem alfab ́etica.
select * from moradores 
order by moradores.nome_morador;

