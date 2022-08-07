<?php

namespace Tests;

interface FrontendTest
{

    public function cannot_access_route_as_guest();

    public function can_access_route_as_user();

    public function can_render_the_component();

    public function can_view_component();

}