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
cont_id int,
cont_calle varchar(29) ,
cont_estado varchar(10),
primary key(cont_id),
check (cont_estado='funcional' or cont_estado='roto' or cont_estado='desbordado') 
);

drop table contenedor;
create table camion(
cam_matricula char(7) primary key,
cam_tipo varchar(10),
cam_modelo varchar(13),
cam_estado varchar(9),/* eventualmente hace un alter table para añdir el check*/
check (cam_estado='funcional' || cam_estado='roto'));
drop table camion;
insert into camion values('SAD0003', 'ruta', 'caterpillar', 'funcional');

create table centro_acopio(
id_acopio int primary key,
calle_acopio varchar(15),
puerta_acopio int,
capacidad int,
volumen_llenado decimal(3, 2) default 0,
check (volumen_llenado<=1));

create table ruta(
ruta_id int,
ruta_fecha date,
ruta_camion varchar(7),
primary key(ruta_id, ruta_fecha),
constraint foreign key (ruta_camion) references camion(cam_matricula));

create table ruta_contenedor(
ruta_id int,
ruta_fecha date,
cont_id int,
vaciado boolean,
volumen_cargado int,
primary key (ruta_id, ruta_fecha, cont_id),
constraint foreign key (ruta_id, ruta_fecha) references ruta(ruta_id, ruta_fecha),
constraint foreign key(cont_id) references contenedor(cont_id));
drop table ruta
        