<?php

namespace ATSearchBundle\Elastic\Generator;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class IndexDocumentMetadataGenerator
{
    private static bool $classMapLoaded = false;

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly IndexDocumentBuilder $indexDocumentBuilder,
        private readonly array $mappings = [],
        public ?string $cacheBaseDir = null,
        private readonly bool $elasticEnabled = false,
    ) {
    }

    public function compile($mapOnly = false): array
    {
        $cacheDir = $this->getCacheDir();
        if (!$mapOnly && $this->filesystem->exists($cacheDir)) {
            $this->filesystem->remove($cacheDir);
        }

        $classes = [[]];
        foreach ($this->mappings as $mapping) {
            $classMap = $this->generateClasses($mapping, $mapOnly);
            $classes[] = $classMap;
        }

        $classes = array_merge(...$classes);
        if ($mapOnly || !$this->elasticEnabled) {
            return $classes;
        }
        $content = "<?php\nreturn " . var_export($classes, true) . ';';

        $content = str_replace(" => '$cacheDir", " => __DIR__ . '", $content);

        $this->filesystem->dumpFile($this->getClassesMap(), $content);

        $this->loadClasses(true);

        return $classes;
    }

    public function loadClasses(bool $forceReload = false): void
    {
        if (!self::$classMapLoaded || $forceReload) {
            $classMapFile = $this->getClassesMap();
            $classes = file_exists($classMapFile) ? require $classMapFile : [];

            /** @var ClassLoader $mapClassLoader */
            static $mapClassLoader = null;

            if (null === $mapClassLoader) {
                $mapClassLoader = new ClassLoader();
                $mapClassLoader->setClassMapAuthoritative(true);
            } else {
                $mapClassLoader->unregister();
            }

            $mapClassLoader->addClassMap($classes);
            $mapClassLoader->register();

            self::$classMapLoaded = true;
        }
    }

    private function getCacheDir(): ?string
    {
        return $this->cacheBaseDir . '/at-search-bundle';
    }

    private function getClassesMap(): string
    {
        return $this->getCacheDir() . '/__classes.map';
    }

    private function generateClasses(array $mapping, bool $mapOnly = false): array
    {
        $classes = [];
        $outputDirectory = $this->getCacheDir();
        $finder = new Finder();
        $files = $finder->files()->in($mapping['dir'])->name('*.php')->files();
        /** @var SplFileInfo $class */
        foreach ($files as $file) {
            $className = explode('.', $file->getFilename())[0] ?? null;
            if (!$className) {
                continue;
            }

            $fileBuilder = $this->indexDocumentBuilder->build($mapping['namespace'], $className);
            if (!$fileBuilder) {
                continue;
            }
            $documentClassName = $className . 'IndexDocument';
            if (!$mapOnly) {
                $fileBuilder->save($outputDirectory . '/' . $documentClassName . '.php');
            }

            $path = "$outputDirectory/$documentClassName.php";
            $classes["ATSearchBundle\\DocumentMetadata\\$documentClassName"] = $path;

        }

        return $classes;
    }
}