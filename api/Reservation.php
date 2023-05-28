<?php

class Reservation{

    private $idRoom, $checkInDate, $checkOutDate, $fullName, $phone;

    private $conn;

    public function __construct() {
        $this->conn = new mysqli('localhost', 'thyler', 'k', 'hotel_reservation_system');

        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }

    public function create_reservation($checkInDate, $checkOutDate, $fullName, $phone, $floor)
    {
        $res = array('error' => false);
        
        $idRoom = $this->select_next_available_room_id('R' . $floor . '%');
        $reservationDate = date('Y-m-d');

        if(strtotime($checkInDate) == false || strtotime($checkOutDate) == false || strtotime($checkInDate) < strtotime(date('Y-m-d')) || strtotime($checkInDate) > strtotime($checkOutDate) ){
            $res['message'] = 'Check date';
            return $res;
        }

        if(strtotime(date('Y-m-d')) < strtotime($checkInDate)){
            $stat = 'On going';
        }
        
        if ($idRoom !== null) {
            if($this->conn->query("INSERT INTO reservation (id_room, check_in_date, check_out_date, reservation_date, full_name, phone, stat) VALUES ('$idRoom', '$checkInDate', '$checkOutDate', '$reservationDate', '$fullName', '$phone','$stat')")){
                $res['message'] = 'Reservation created successfully';
                $res['reservationId'] = $stmt->insert_id;
                $res ['details'] = $idRoom.":".$reservationDate.":".$fullName.":".$phone;

            } else {
                echo "nope";
                $res['error'] = true;
                $res['message'] = 'Error creating reservation: ' . $stmt->error;
            }
        
        } else {
            $res['error'] = true;
            $res['message'] = 'No available room found for the specified floor';
        }
        
        return $res;
    }


    public function list_reservations()
    {
        $res = array('error' => false);
        
        $stmt = $this->conn->prepare("SELECT * FROM reservation");
        $stmt->execute();
        
        $result = $stmt->get_result();
        $reservations = $result->fetch_all(MYSQLI_ASSOC);
        
        if ($reservations) {
            $res['reservations'] = $reservations;
        } else {
            $res['error'] = true;
            $res['message'] = 'No reservations found';
        }

        $stmt->close();
        return $res;
    }


    public function update_reservation($idRoom, $checkInDate, $checkOutDate, $fullName, $nphone)
    {
        $res = array('error' => false);
        
        
        $stmt = $this->conn->prepare("UPDATE reservation SET check_in_date = ?, check_out_date = ?, full_name = ?, phone = ? WHERE id_room = ?");
        $stmt->bind_param("sssss", $checkInDate, $checkOutDate, $fullName, $nphone, $idRoom);
        
        if ($stmt->execute()) {
            $res['message'] = 'Reservation updated successfully';
        } else {
            $res['error'] = true;
            $res['message'] = 'Error updating reservation: ' . $stmt->error;
        }

        $stmt->close();
        return $res;
    }

    public function cancel_reservation($reservationDate)
    {
        $res = array('error' => false);
        
        $stmt = $this->conn->prepare("delete from reservation where reservation_date = '$reservationDate' and phone = '$phone'");
        $stmt->bind_param("i", $reservationDate);
        
        if ($stmt->execute()) {
            $res['message'] = 'Reservation cancelled successfully';
        } else {
            $res['error'] = true;
            $res['message'] = 'Error deleting reservation: ' . $stmt->error;
        }

        $stmt->close();
        return $res;
    }

    public function select_next_available_room_id($floor)
    {
        $stmt = $this->conn->prepare("SELECT room_number FROM room WHERE room_number LIKE ? AND room_number NOT IN (SELECT id_room FROM reservation) LIMIT 1");
        $stmt->bind_param("s", $floor);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['room_number'];
        } else {
            return null; // No available room found
        }
    }

    public function search_reservation($searchTerm)
    {
        $res = array('error' => false);
        
        $stmt = $this->conn->prepare("SELECT * FROM reservation WHERE full_name LIKE ? OR phone = ? OR DATE(reservation_date) = ?");
        $searchTerm = '%' . $searchTerm . '%';
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $reservations = $result->fetch_all(MYSQLI_ASSOC);
        
        if ($reservations) {
            $res['reservations'] = $reservations;
        } else {
            $res['error'] = true;
            $res['message'] = 'No reservations found for the given search term';
        }

        $stmt->close();
        return $res;
    }



}

?>