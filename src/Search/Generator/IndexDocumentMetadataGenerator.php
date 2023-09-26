<?php

namespace ATSearchBundle\Search\Generator;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class IndexDocumentMetadataGenerator
{
    private static bool $classMapLoaded = false;

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly IndexDocumentBuilder $indexDocumentBuilder,
        private readonly array $mappings = [],
        public ?string $cacheBaseDir = null,
        private readonly bool $searchEnabled = false,
    ) {
    }

    public function compile(): array
    {
        $cacheDir = $this->getCacheDir();
        if ($this->filesystem->exists($cacheDir)) {
            $this->filesystem->remove($cacheDir);
        }

        $classes = [[]];
        foreach ($this->mappings as $mapping) {
            $classMap = $this->generateClasses($mapping);
            $classes[] = $classMap;
        }

        $classes = array_merge(...$classes);
        if (!$this->searchEnabled) {
            return $classes;
        }
        $content = "<?php\nreturn " . var_export($classes, true) . ';';

        $content = str_replace(" => '$cacheDir", " => __DIR__ . '", $content);

        $this->filesystem->dumpFile($this->getClassesMap(), $content);

        $this->loadClasses(true);

        return $classes;
    }

    public function compileClassesForTags(): array
    {
        $classes = [[]];
        foreach ($this->mappings as $mapping) {
            $classMap = $this->generateClassesWithPriority($mapping);
            $classes[] = $classMap;
        }

        return array_merge(...$classes);
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

    private function generateClasses(array $mapping): array
    {
        $classes = [];
        $outputDirectory = $this->getCacheDir();
        $classNames = $this->getClassNames($mapping);
        foreach ($classNames as $className) {
            $fileBuilder = $this->indexDocumentBuilder->build($mapping['namespace'], $className);
            if (!$fileBuilder) {
                continue;
            }
            $documentClassName = $className . 'IndexDocument';
            $fileBuilder->save($outputDirectory . '/' . $documentClassName . '.php', 511);

            $path = "$outputDirectory/$documentClassName.php";
            $classes["ATSearchBundle\\DocumentMetadata\\$documentClassName"] = $path;
        }

        return $classes;
    }

    private function getClassNames(array $mapping): array
    {
        $classNames = [];
        $finder = new Finder();
        $files = $finder->files()->in($mapping['dir'])->name('*.php')->files();
        foreach ($files as $file) {
            $className = explode('.', $file->getFilename())[0] ?? null;
            if (!$className) {
                continue;
            }
            $classNames[] = $className;
        }

        return $classNames;
    }

    private function generateClassesWithPriority(array $mapping): array
    {
        $classes = [];
        $classNames = $this->getClassNames($mapping);
        foreach ($classNames as $className) {
            $fileBuilder = $this->indexDocumentBuilder->build($mapping['namespace'], $className);
            if (!$fileBuilder) {
                continue;
            }

            $filePriority = $this->indexDocumentBuilder->getClassPriority($mapping['namespace'], $className);
            $classes["ATSearchBundle\\DocumentMetadata\\{$className}IndexDocument"] = $filePriority;
        }

        return $classes;
    }
}