<?php

namespace Hypweb\Flysystem\ZipArchive;

use League\Flysystem\ZipArchive\ZipArchiveAdapter as AbstractAdapter;
use League\Flysystem\Util;

class ZipArchiveAdapter extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        $source = $this->applyPathPrefix($path);
        $destination = $this->applyPathPrefix($newpath);
        $info = $this->archive->statName($source);

        if ($info && substr($info['name'], -1) !== '/') {

            return $this->archive->renameName($source, $destination);

        } 

        $source = Util::normalizePrefix($source, '/');
        $destination = Util::normalizePrefix($destination, '/');
        $length = strlen($source);
        $result = true;

        // This is needed to ensure the right number of
        // files are set to the $numFiles property.
        $this->reopenArchive();

        for ($i = 0; $i < $this->archive->numFiles; $i++) {
            $info = $this->archive->statIndex($i);

            if (substr($info['name'], 0, $length) === $source) {
                if (! $result = $this->archive->renameIndex($i, $destination . substr($info['name'], $length))) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        // This is needed to ensure the right number of
        // files are set to the $numFiles property.
        $this->reopenArchive();

        $location = $this->applyPathPrefix($dirname);
        $path = Util::normalizePrefix($location, '/');
        $length = strlen($path);

        for ($i = 0; $i < $this->archive->numFiles; $i++) {
            $info = $this->archive->statIndex($i);

            if (substr($info['name'], 0, $length) === $path && $info['name'] !== $path) {
                $this->archive->deleteIndex($i);
            }
        }

        return $this->archive->deleteName($path);
    }
}
