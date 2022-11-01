<?php

class Auths
{

    function index()
    {

        $ci = &get_instance();
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        if ($ci->uri->segment(1) != 'api') return null;

        $ci->load->model("table_model");

        $ci->table_model->tableName = "users";

        if ($ci->uri->segment(2) == 'tables') {
            //headerden token alma
            $_Header = $ci->input->request_headers();
            $token = $_Header['token'] ?? $_Header['Token'];

            if (empty($token) || $token == 'null') $token = '0';

            //dd($token);
            //kullanıcı kontrolü
            $user_id = $ci->table_model->get(['token' => $token])->auths_group_id ?? 3;
            if ($user_id == 0) {
                return true;
            }
            if ($user_id == 0) {
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                $ci->output->set_status_header(401);
                exit;
            }
        }
    }
}
