<?php
/**
 * Created by PhpStorm.
 * User: loic
 * Date: 31/10/17
 * Time: 11:55
 */

class centreonFileManager implements iFileManager
{

    protected $rawFile;
    protected $dependencyInjector;
    protected $comment;
    protected $tmpFile;
    protected $mediaPath;
    protected $destinationPath;
    protected $destinationDir;
    protected $originalFile;
    protected $fileName;
    protected $size;
    protected $extension;
    protected $newFile;
    protected $completePath;

    /**
     * centreonFileManager constructor.
     * @param \Pimple\Container $dependencyInjector
     * @param $rawFile
     * @param $mediaPath
     * @param $destinationDir
     * @param string $comment
     */
    public function __construct(
        \Pimple\Container $dependencyInjector,
        $rawFile,
        $mediaPath,
        $destinationDir,
        $comment = ''
    ) {

        $this->dependencyInjector = $dependencyInjector;
        $this->mediaPath = $mediaPath;
        $this->comment = $comment;
        $this->rawFile = $rawFile["filename"];
        $this->destinationDir = $this->secureName($destinationDir);
        $this->destinationPath = $this->mediaPath . $this->destinationDir;
        $this->dirExist($this->destinationPath);
        $this->originalFile = $this->rawFile['name'];
        $this->tmpFile = $this->rawFile['tmp_name'];
        $this->size = $this->rawFile['size'];
        $this->extension = pathinfo($this->originalFile, PATHINFO_EXTENSION);
        $this->fileName = $this->secureName(basename($this->originalFile, '.' . $this->extension));
        $this->newFile = $this->fileName . '.' . $this->extension;
        $this->completePath = $this->destinationPath . '/' . $this->newFile;
    }

    /**
     * @return mixed
     */
    public function upload()
    {
        if ($this->securityCheck()) {
            $this->moveFile();
        };
    }

    /**
     * @return bool
     */
    protected function securityCheck()
    {
        if (!$this->validFile($this->tmpFile, $this->size) ||
            !$this->validSize() ||
            !$this->secureExtension($this->extension) ||
            $this->fileExist($this->completePath)
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $text
     * @return mixed
     */
    protected function secureName($text)
    {
        $utf8 = array(
            '/[áàâãªä]/u' => 'a',
            '/[ÁÀÂÃÄ]/u' => 'A',
            '/[ÍÌÎÏ]/u' => 'I',
            '/[íìîï]/u' => 'i',
            '/[éèêë]/u' => 'e',
            '/[ÉÈÊË]/u' => 'E',
            '/[óòôõºö]/u' => 'o',
            '/[ÓÒÔÕÖ]/u' => 'O',
            '/[úùûü]/u' => 'u',
            '/[ÚÙÛÜ]/u' => 'U',
            '/ç/' => 'c',
            '/Ç/' => 'C',
            '/ñ/' => 'n',
            '/Ñ/' => 'N',
            '/–/' => '-',
            '/[“”«»„"’‘‹›‚]/u' => '',
            '/ /' => '',
            '/\//' => '',
            '/\'/' => '',
        );
        return preg_replace(array_keys($utf8), array_values($utf8), $text);
    }

    /**
     * @param $extension
     * @return bool
     */
    protected function secureExtension($extension)
    {
        if (in_array(strtolower($extension), $this->legalExtensions)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $file
     * @param $size
     * @return bool
     */
    protected function validFile($file, $size)
    {
        if (empty($file) || $size == 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return bool
     */
    protected function validSize()
    {
        if ($this->size < $this->legalSize) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $pathFile
     * @return bool
     */
    protected function fileExist($pathFile)
    {
        if (file_exists($pathFile)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $dir
     */
    protected function dirExist($dir)
    {
        if (!is_dir($dir)) {
            @mkdir($dir);
        }
    }

    /**
     * @return mixed
     */
    protected function moveFile()
    {
        move_uploaded_file($this->tmpFile, $this->completePath);
    }
}
