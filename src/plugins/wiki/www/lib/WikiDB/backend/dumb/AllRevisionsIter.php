<?php

/**
 * An iterator which returns all revisions of page.
 *
 * This iterator uses  only the WikiDB_backend::get_versiondata interface
 * of a WikiDB_backend, and so it should work with all backends.
 */
class WikiDB_backend_dumb_AllRevisionsIter
    extends WikiDB_backend_iterator
{
    /**
     * @param $backend object A WikiDB_backend.
     * @param $pagename string Page whose revisions to get.
     */
    function __construct(&$backend, $pagename)
    {
        $this->_backend = &$backend;
        $this->_pagename = $pagename;
        $this->_lastversion = -1;
    }

    /**
     * Get next revision in sequence.
     *
     * @see WikiDB_backend_iterator_next;
     */
    function next()
    {
        $backend = &$this->_backend;
        $pagename = &$this->_pagename;
        $version = &$this->_lastversion;

        //$backend->lock();
        if ($this->_lastversion == -1)
            $version = $backend->get_latest_version($pagename);
        elseif ($this->_lastversion > 0)
            $version = $backend->get_previous_version($pagename, $version);

        if ($version)
            $vdata = $backend->get_versiondata($pagename, $version);
        //$backend->unlock();

        if ($version == 0)
            return false;

        if (is_string($vdata) and !empty($vdata)) {
            $vdata1 = @unserialize($vdata);
            if (empty($vdata1)) {
                if (DEBUG) // string but unseriazible
                    trigger_error("Broken page $pagename ignored. Run Check WikiDB", E_USER_WARNING);
                return false;
            }
            $vdata = $vdata1;
        }
        $rev = array('versiondata' => $vdata,
            'pagename' => $pagename,
            'version' => $version);

        if (!empty($vdata['%pagedata'])) {
            $rev['pagedata'] = $vdata['%pagedata'];
        }

        return $rev;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
