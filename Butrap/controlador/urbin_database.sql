create database urbin;
use urbin;
create table usuario(
usr_ci int not null primary key,
usr_name varchar(40),
usr_email varchar(50),
usr_rol varchar(21),
usr_telefono int);
use urbin;
insert into usuario values (1, 'pepe', 'pepe@gmail.com', 'administrador', 1);
delete from usuario where usr_ci = 1;
select * from contenedor;
alter table usuario
modify  usr_telefono int not null;

create table contenedor(
cont_id int primary key,
cont_calle varchar(29) ,
cont_estado varchar(10)
);

create table camion(
cam_matricula char(7) primary key,
cam_tipo varchar(10),
cam_modelo varchar(13),
cam_estado varchar(9));


