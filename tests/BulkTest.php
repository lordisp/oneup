<?php

namespace Tests;

interface BulkTest
{
    public function can_select_one_or_more_rows();

    public function can_unselect_one_or_more_rows();

    public function can_select_all_rows_on_first_page();

    public function can_unselect_all_raws_on_first_page();

    public function can_select_all_rows_on_second_page();

    public function can_unselect_all_rows_on_second_page();

    public function page_popup_disappears_if_all_rows_are_selected();

    public function can_delete_two_selected_clients();

    public function can_delete_selected_page();
}
