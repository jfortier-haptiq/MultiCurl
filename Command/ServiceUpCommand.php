<?php

namespace MRX\ServiceTalkBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

//use Symfony\Component\Yaml\Parser as YAMLParser;

class ServiceUpCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('service:up')
            ->setDescription('Announce that your service is up an running')
            //->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
            //->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Access the service containers as you would in a controller
        $this->container = $this->getApplication()->getKernel()->getContainer();
        
        $app_root =  __DIR__ . '/../../../../';
        $file = $app_root . 'servicelocator.yml';
        
        if(!file_exists($file))
        {
            throw new \Exception("Could not locate servicelocator.yml in project root.");
        }
        
        $value = $this->container->get('yaml')->parse( file_get_contents($file) );
        
        $responses = $this->container->get('curl')
            ->open()
                ->setServer('127.0.0.1')
                ->setUrl("http://api.logger.jfortier.mrx.ca/app_dev.php/changelog/object/systemlog/1.xml")
            ->close()
            ->open()
                ->setServer('127.0.0.1')
                ->setUrl("http://api.logger.jfortier.mrx.ca/app_dev.php/changelog/object/systemlog/10.xml")
            ->close()
            ->open()
                ->setUrl("http://google.ca")
            ->close()
        ->send();
                        
        print_r ( $responses );
                
        ///$output->writeln(  );
    }
}

