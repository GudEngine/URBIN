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

/*drop table contenedor;*/
create table camion(
cam_matricula char(7) primary key,
cam_tipo varchar(10),
cam_modelo varchar(13),
cam_estado varchar(9),
check (cam_estado='funcional' || cam_estado='roto'));
drop table camion;
insert into camion values('SAD0003', 'ruta', 'caterpillar', 'funcional');

create table centro_acopio(
acopio_id int primary key,
acopio_operario int,
acopio_tipo_residuo varchar(15),
acopio_calle varchar(15),
acopio_puerta int,
acopio_capacidad int,
acopio_volumen_llenado decimal(3, 2) default 0,
check (acopio_volumen_llenado<=1),
constraint foreign key(acopio_operario) references usuario(usr_ci));


create table ruta(
ruta_id int,
ruta_fecha date,
ruta_camion varchar(7),
ruta_acopio int,
volumen_total int,
primary key(ruta_id, ruta_fecha),
constraint foreign key (ruta_camion) references camion(cam_matricula),
constraint foreign key (ruta_acopio) references centro_acopio(acopio_id));

create table ruta_contenedor(
ruta_id int,
ruta_fecha date,
cont_id int,
vaciado boolean,
volumen_cargado int,/*desde bd*/
volumen_descarga int,
primary key (ruta_id, ruta_fecha, cont_id),
constraint fecha_contenedor unique (ruta_fecha, cont_id),
constraint foreign key (ruta_id, ruta_fecha) references ruta(ruta_id, ruta_fecha),
constraint foreign key(cont_id) references contenedor(cont_id));

create table cuadrilla(
cuad_ci int primary key,/* cedula del recolector*/
cuad_cam char(7),
constraint foreign key(cuad_ci) references usuario(usr_ci),
constraint foreign key(cuad_cam) references camion(cam_matricula)
);

create table camion_ruta(
cam_ruta_camion varchar(7),
cam_ruta_ruta_id int,
cam_ruta_fecha int,
primary key(cam_ruta_ruta_id, cam_ruta_fecha),
constraint foreign key(cam_ruta_camion) references camion(cam_matricula),
constraint foreign key(cam_ruta_ruta_id) references ruta(ruta_id),
constraint foreign key(cam_ruta_fecha) references ruta(ruta_fecha)
);

create table vertedero(
nom_vertedero varchar(15) primary key,
calle_vertedero varchar(15),
puerta_vertedero int,
capacidad_vertedero int,
volumen_llenado_vertedero decimal(3, 2) default 0,
check (volumen_llenado_acopio<=1));



        