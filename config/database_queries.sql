create database stock_management;
create table users (
    id int not null auto_increment primary key,
    username varchar(100),
    password varchar(200)
);
create table stocks (
    id int not null auto_increment primary key,
    item_name varchar(100),
    quantity int,
    price varchar(20),
    user_id int
);
create table invoices (
    id int not null auto_increment primary key,
    item_name varchar(255),
    quantity int,
    total int,
    user_id int not null,
    created_at datetime default current_timestamp not null
);