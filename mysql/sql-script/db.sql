
CREATE DATABASE code_challenge_db
DEFAULT CHARACTER SET 'utf8mb4'
COLLATE 'utf8mb4_spanish_ci';

CREATE USER 'code_challenge_u'@'%' IDENTIFIED BY 'code_challenge_2022';
GRANT ALL PRIVILEGES ON *.* TO 'code_challenge_u'@'%';
FLUSH PRIVILEGES;

use code_challenge_db;

delimiter |
create procedure error (in in_code varchar(255)) begin 
	signal sqlstate '45000' set message_text = in_code;
end |
delimiter ;

delimiter |
create procedure denied() begin 
	call error('denied');
end |
delimiter ;

CREATE TABLE cc_movement (
	mo_id bigint unsigned auto_increment primary key,
	mo_subject varchar(255),
	mo_amount int,
	mo_invoice text,
	mo_type varchar(50),
	mo_date date,
	mo_user_name varchar(255),
	mo_user_email varchar(255),
	mo_register datetime,
	mo_status boolean
);

delimiter |
	create trigger cc_movement_in before insert on cc_movement for each row begin

		if length(trim(new.mo_subject)) = 0 then 
			call error('subject-needed');
		end if;

		if new.mo_amount <= 0 then 
			call error('amount-meeded');
		end if;

		if length(trim(new.mo_subject)) = 0 then 
			call error('invoce-needed');
		end if;

		if new.mo_type not in ('income','expense') then 
			call error('wrong-type');
		end if;

		if length(trim(new.mo_user_name)) = 0 then 
			call error('username-needed');
		end if;

		if length(trim(new.mo_user_email)) = 0 then 
			call error('useremail-needed');
		end if;

		set new.mo_register = now();
		set new.mo_status = 1;
	end |
delimiter ;

delimiter |
	create trigger cc_movement_upd before update on cc_movement for each row begin

		if length(trim(new.mo_subject)) = 0 then 
			call error('subject-needed');
		end if;

		if new.mo_amount <= 0 then 
			call error('amount-meeded');
		end if;

		if length(trim(new.mo_subject)) = 0 then 
			call error('invoce-needed');
		end if;

		if new.mo_type not in ('income','expense') then 
			call error('wrong-type');
		end if;

		if length(trim(new.mo_user_name)) = 0 then 
			call error('username-needed');
		end if;

		if length(trim(new.mo_user_email)) = 0 then 
			call error('useremail-needed');
		end if;

		if old.mo_register <> new.mo_register then
			call denied();
		end if;
	end |
delimiter ;
delimiter |
	create trigger cc_movement_del before delete on cc_movement for each row begin
		call denied();
	end |
delimiter ;

/* Funciones */
delimiter | 
	create function cc_movement_add (
		in_mo_subject varchar(255),
		in_mo_amount int,
		in_mo_invoice text,
		in_mo_type varchar(50),
		in_mo_date date,
		in_mo_user_name varchar(255),
		in_mo_user_email varchar(255)
	) returns bigint unsigned deterministic begin
		declare new_mo_id bigint unsigned;

		insert into cc_movement(
			mo_subject,
			mo_amount,
			mo_invoice,
			mo_type,
			mo_date,
			mo_user_name,
			mo_user_email
		) values(
			in_mo_subject,
			in_mo_amount,
			in_mo_invoice,
			in_mo_type,
			in_mo_date,
			in_mo_user_name,
			in_mo_user_email
		);
		
		set new_mo_id = LAST_INSERT_ID();
			
		return new_mo_id;
	end | 
delimiter ;

delimiter | 
	create function cc_movement_upd (
		in_mo_id bigint unsigned,
		in_mo_subject varchar(255),
		in_mo_amount int,
		in_mo_invoice text,
		in_mo_type varchar(50),
		in_mo_date date,
		in_mo_user_name varchar(255),
		in_mo_user_email varchar(255)
	) returns bigint unsigned deterministic begin

		if not exists(select mo_id from cc_movement where mo_id = in_mo_id) then 
			call error('movement-not-exists');
		end if;
		
		update cc_movement set
			mo_subject = in_mo_subject,
			mo_amount = in_mo_amount,
			mo_invoice = in_mo_invoice,
			mo_type = in_mo_type,
			mo_date = in_mo_date,
			mo_user_name = in_mo_user_name,
			mo_user_email = in_mo_user_email
		where 
			mo_id = in_mo_id;
			
		return in_mo_id;
	end | 
delimiter ;

delimiter | 
	create function cc_movement_del (
		in_mo_id bigint unsigned
	) returns text deterministic begin

		if not exists(select mo_id from cc_movement where mo_id = in_mo_id) then 
			call error('movement-not-exists');
		end if;
		
		update cc_movement set
			mo_status = 0
		where 
			mo_id = in_mo_id;
			
		return 'movement-deleted';
	end | 
delimiter ;