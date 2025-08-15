<?php

if(!function_exists('has_sample_procedures')) {
    /**
     * Check if sample procedures are enabled in the configuration.
     *
     * @return bool
     */
    function has_sample_procedures(): bool
    {
        return (bool) config('rpc.sample_procedures', false);
    }
}
