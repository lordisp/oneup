<?php

namespace Tests;

interface FrontendTest
{
    /** test */
    public function cannot_access_route_as_guest();

    /** test */
    public function can_access_route_as_user();

    /** test */
    public function can_render_the_component();

    /** test */
    public function can_view_component();

}