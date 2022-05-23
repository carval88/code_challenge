<?php
class Movement
{
     public ?int $mo_id;
     public string $mo_subject;
     public int $mo_amount;
     public string $mo_invoice;
     public string $mo_type;
     public string $mo_date;
     public string $mo_user_name;
     public string $mo_user_email;
     public ?string $mo_register;
     public ?string $mo_status;

     public function __construct()
     {
          $arguments = func_get_args();
          $numberOfArguments = func_num_args();


          switch ($numberOfArguments) {
               case 1:
                    if (is_array($arguments[0])) {
                         if (method_exists($this, $function = '__constructArray')) {
                              call_user_func_array(array($this, $function), $arguments);
                         }
                    } else {
                         if (method_exists($this, $function = '__construct' . $numberOfArguments)) {
                              call_user_func_array(array($this, $function), $arguments);
                         }
                    }
                    break;
               default:
                    if (method_exists($this, $function = '__construct' . $numberOfArguments)) {
                         call_user_func_array(array($this, $function), $arguments);
                    }
                    break;
          }
     }

     public function __construct1(
          int $mo_id
     ) {
          $this->__set('mo_id', $mo_id);
     }

     public function __construct10(
          ?int $mo_id,
          string $mo_subject,
          int $mo_amount,
          string $mo_invoice,
          ?string $mo_type,
          string $mo_date,
          string $mo_user_name,
          string $mo_user_email,
          ?string $mo_register,
          ?string $mo_status
     ) {
          try {
               if (!is_null($mo_id))
                    $this->mo_id             = $mo_id;

               $this->mo_subject             = $mo_subject;
               $this->mo_amount              = $mo_amount;
               $this->mo_invoice             = $mo_invoice;
               if (!is_null($mo_type))
                    $this->mo_type           = $mo_type;

               $this->mo_date                = $mo_date;
               $this->mo_user_name           = $mo_user_name;
               $this->mo_user_email          = $mo_user_email;
               if (!is_null($mo_register))
                    $this->mo_register       = $mo_register;

               if (!is_null($mo_status))
                    $this->mo_status         = $mo_status;
          } catch (Exception | TypeError $e) {
               IOException::set($e->getMessage());
          }
     }

     public function __constructArray(array $properties)
     {
          foreach ($properties as $key => $pp) {
               $this->__set($key, $pp);
          }
     }

     public function __set($name, $value)
     {
          if (property_exists(__CLASS__, $name))
               $this->$name = $value;
     }

     public function __get($name)
     {
          return $this->$name;
     }

     public function __read(): Movement
     {
          try {
               $st = mysqli_prepare(ISQL::on(), "
			select
                    mo_id,
				mo_subject,
				mo_amount,
                    mo_invoice,
				mo_type,
				mo_date,
				mo_user_name,
				mo_user_email,
				mo_register,
				mo_status
			from
				cc_movement
			where
				mo_id = ?
		");
               if (!$st)
                    throw new Exception(mysqli_error(ISQL::on()));

               $st->bind_param(
                    'i',
                    $this->mo_id
               );
               $st->execute();
               $r = $st->get_result();

               if (!$r)
                    throw new Exception(mysqli_error(ISQL::on()));

               return $r->fetch_object(__CLASS__) ?: new Movement();
          } catch (\Throwable $th) {
               IOException::set($th->getMessage());
          }
     }

     public function __create(): Movement
     {

          try {
               ISQL::on()->autocommit(false);

               $st = mysqli_prepare(ISQL::on(), "select cc_movement_add(?,?,?,?,?,?,?) as mo_id");

               if (!$st)
                    throw new Exception(mysqli_error(ISQL::on()));

               $st->bind_param(
                    "sisssss",
                    $this->mo_subject,
                    $this->mo_amount,
                    $this->mo_invoice,
                    $this->mo_type,
                    $this->mo_date,
                    $this->mo_user_name,
                    $this->mo_user_email
               );

               $st->execute();

               $r = $st->get_result();

               if (!$r)
                    throw new Exception(mysqli_error(ISQL::on()));

               $r = $r->fetch_assoc();
               $this->__set('mo_id', $r['mo_id']);
               ISQL::on()->commit();

               return $this->__read();
          } catch (\Throwable $th) {
               IOException::set($th->getMessage());
          }
     }

     public function __update(): Movement
     {
          try {
               ISQL::on()->autocommit(false);

               $st = mysqli_prepare(ISQL::on(), "select cc_movement_upd(?,?,?,?,?,?,?,?) as mo_id");

               if (!$st)
                    throw new Exception(mysqli_error(ISQL::on()));

               $st->bind_param(
                    "isisssss",
                    $this->mo_id,
                    $this->mo_subject,
                    $this->mo_amount,
                    $this->mo_invoice,
                    $this->mo_type,
                    $this->mo_date,
                    $this->mo_user_name,
                    $this->mo_user_email
               );

               $st->execute();

               $r = $st->get_result();

               if (!$r)
                    throw new Exception(mysqli_error(ISQL::on()));

               $r = $r->fetch_assoc();
               $this->__set('mo_id', $r['mo_id']);
               ISQL::on()->commit();

               return $this->__read();
          } catch (\Throwable $th) {
               IOException::set($th->getMessage());
          }
     }

     public function __delete(): String
     {
          try {
               ISQL::on()->autocommit(false);

               $st = mysqli_prepare(ISQL::on(), "select cc_movement_del(?) as r");

               if (!$st)
                    throw new Exception(mysqli_error(ISQL::on()));

               $st->bind_param(
                    "i",
                    $this->mo_id
               );

               $st->execute();

               $r = $st->get_result();

               if (!$r)
                    throw new Exception(mysqli_error(ISQL::on()));

               $r = $r->fetch_assoc();
               ISQL::on()->commit();

               return IOLanguage::translate($r);
          } catch (\Throwable $th) {
               IOException::set($th->getMessage());
          }
     }

     public static function readAll(int $page = 1): array
     {
          $rpp = 10;
          $page = (($page < 1 ? 1 : $page) - 1) * $rpp;
          $mo_status = 1;

          try {
               $st = mysqli_prepare(ISQL::on(), "
                    select
                         mo_id,
                         mo_subject,
                         mo_amount,
                         mo_invoice,
                         mo_type,
                         mo_date,
                         mo_user_name,
                         mo_user_email,
                         mo_register,
                         mo_status
                    from
                         cc_movement
                    where
                         mo_status = ?
                    limit
                         ?, ?
               ");
               if (!$st)
                    throw new Exception(mysqli_error(ISQL::on()));

               $st->bind_param(
                    "i,i,i",
                    $mo_status,
                    $page,
                    $rpp
               );
               $st->execute();
               $r = $st->get_result();
               if (!$r)
                    throw new Exception(mysqli_error(ISQL::on()));

               $movements = array();
               while ($mo = $r->fetch_assoc()) {
                    $movements[] = $mo;
               }

               return $movements;
          } catch (\Throwable $th) {
               IOException::set($th->getMessage());
          }
     }

     public static function incomesSummary(int $month): int
     {
          $mo_type = 'income';
          try {
               $st = mysqli_prepare(ISQL::on(), "
                    select
                         ifnull(sum(mo_amount),0) as total
                    from
                         cc_movement
                    where
                         mo_status = 1
                    and 
                         mo_type = ?
                    and
                         MONTH(mo_date) = ?
               ");
               if (!$st)
                    throw new Exception(mysqli_error(ISQL::on()));

               $st->bind_param(
                    "si",
                    $mo_type,
                    $month
               );
               $st->execute();
               $r = $st->get_result();
               if (!$r)
                    throw new Exception(mysqli_error(ISQL::on()));

               $r = $r->fetch_assoc();

               return $r['total'];
          } catch (\Throwable $th) {
               IOException::set($th->getMessage());
          }
     }

     public static function expensesSummary(int $month): int
     {
          $mo_type = 'expense';
          try {
               $st = mysqli_prepare(ISQL::on(), "
                    select
                         ifnull(sum(mo_amount),0) as total
                    from
                         cc_movement
                    where
                         mo_status = 1
                    and 
                         mo_type = ?
                    and
                         MONTH(mo_date) = ?
               ");
               if (!$st)
                    throw new Exception(mysqli_error(ISQL::on()));

               $st->bind_param(
                    "si",
                    $mo_type,
                    $month
               );
               $st->execute();
               $r = $st->get_result();
               if (!$r)
                    throw new Exception(mysqli_error(ISQL::on()));

               $r = $r->fetch_assoc();

               return $r['total'];
          } catch (\Throwable $th) {
               IOException::set($th->getMessage());
          }
     }
}
