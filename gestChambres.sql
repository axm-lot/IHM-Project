-- create database
DROP DATABASE IF EXISTS hotel_reservation_system;

CREATE DATABASE hotel_reservation_system;

USE hotel_reservation_system;

CREATE TABLE room (
	room_number VARCHAR(10) PRIMARY KEY,
	description TEXT,
	number_of_person INT,
	price REAL
);

CREATE TABLE reservation (
	id INT PRIMARY KEY AUTO_INCREMENT,
	id_room VARCHAR(10) NOT NULL REFERENCES room(room_number),
	reservation_date DATE,
	check_in_date DATE,
	check_out_date DATE,
	full_name VARCHAR(25),
	phone VARCHAR(20)
);
