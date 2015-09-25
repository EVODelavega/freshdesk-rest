<?php

namespace Freshdesk;

/**
 * Model interface
 *
 * @author Ike Devolder <ike.devolder@gmail.com>
 */
interface Model
{
    public function toJsonData($json = true);
}
