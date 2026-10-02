<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function has_approved_partner() {
    $CI =& get_instance();
    $CI->load->model('M_partner');
    $user_id = $CI->session->userdata('user_id');
    return $CI->M_partner->has_approved_partner($user_id);
}

function get_approved_partners() {
    $CI =& get_instance();
    $CI->load->model('M_partner');
    $user_id = $CI->session->userdata('user_id');
    return $CI->M_partner->get_approved_partners($user_id);
}