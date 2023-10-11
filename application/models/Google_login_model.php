<?php
class Google_login_model extends CI_Model
{
    function Is_already_register($id)
    {
        $this->db->where('login_oauth_uid', $id);
        $query = $this->db->get('user');
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    function Is_email_register($email)
    {
        $this->db->where('email', $email);
        $query = $this->db->get('user');
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    function Update_user_data($data, $id)
    {
        $this->db->where('login_oauth_uid', $id);
        $this->db->update('user', $data);
    }

    function Insert_user_data($data)
    {
        $this->db->insert('user', $data);
    }
}
