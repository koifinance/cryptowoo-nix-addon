<?php

namespace BitWasp\Bitcoin\Network;

class NIX_NetworkFactory extends CW_NetworkFactory
{

    /**
     * @return Networks\Vertcoin
     */
    public static function nix()
    {
        return new Networks\Nix();
    }

}
