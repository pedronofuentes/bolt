<?php

namespace Bolt\Database\Migration;

use Bolt\Application;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Dumper;

/**
 * Database records export class
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Export extends AbstractMigration
{
    /** @var Bolt\Application */
    private $app;

    /**
     * Constructor.
     *
     * @param \Bolt\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Write a migration file.
     *
     * This function will determine what type based on extension.
     *
     * @param string  $file   File name
     * @param array   $data   The data to write out
     * @param boolean $append Whether to append or abort file writing if a file exists
     *
     * @return array
     */
    protected function writeMigrationFile($file, $data, $append = false)
    {
        $fs      = new Filesystem();
        $fileObj = new \SplFileInfo($file);
        $ext     = $fileObj->getExtension();

        if ($fs->exists($file) && $append === false) {
            $this->setError(true)->setErrorMessage("Specified file '$file' already exists!");

            return false;
        }

        try {
            $fs->touch($file);
        } catch (IOException $e) {
            $this->setError(true)->setErrorMessage("Specified file '$file' can not be created!");

            return false;
        }

        // Write them out
        if ($ext === 'yml' || $ext === 'yaml') {
            return $this->writeYamlFile($file, $data);
        } else {
            return $this->writeJsonFile($file, $data);
        }
    }

    /**
     * Write a YAML migration file.
     *
     * @param string  $file   File name
     * @param array   $data   The data to write out
     *
     * @return array
     */
    private function writeYamlFile($file, $data)
    {
        // Get a new YAML dumper
        $dumper = new Dumper();

        // Generate the YAML string
        try {
            $yaml = $dumper->dump($data, 4, 0, true);
        } catch (Exception $e) {
            $this->setError(true)->setErrorMessage("Unable to generate valid YAML data!");

            return false;
        }

        if (file_put_contents($file, $yaml, FILE_APPEND) === false) {
            $this->setError(true)->setErrorMessage("Unable to write YAML data to '$file'!");

            return false;
        }

        return true;
    }

    /**
     * Write a JSON migration file.
     *
     * @param string  $file   File name
     * @param array   $data   The data to write out
     *
     * @return array
     */
    private function writeJsonFile($file, $data)
    {
        // Generate the JSON string
        $json = json_encode($data);

        if ($json === false) {
            $this->setError(true)->setErrorMessage("Unable to generate valid JSON data!");

            return false;
        }

        if (file_put_contents($file, $json, FILE_APPEND) === false) {
            $this->setError(true)->setErrorMessage("Unable to write JSON data to '$file'!");

            return false;
        }

        return true;
    }
}
