<?php

namespace System\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SystemUserBundle extends Bundle
{
	public function getParent()
    {
        return 'FOSUserBundle';
    }
}
