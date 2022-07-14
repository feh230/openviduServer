<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 

class openviduServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openvidu:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to fix the openvidu folder';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Fixing the openvidu folder');
        

        // Verifico os arquivos que estão no diretório do servidor
        $folder = scandir('./storage/app/openvidu');
        $folders = array_diff($folder, array('.', '..'));
        $this->line('Files in the openvidu folder:');
        foreach ($folders as $folder) {
            $files = scandir('./storage/app/openvidu/'.$folder);
            $files = array_diff($files, array('.', '..'));
            
            // Vejo se existe arquivi .jpg no diretório
            $jpg = false;
            foreach ($files as $file) {
                if (Str::endsWith($file, '.jpg')) {
                    $jpg = true;
                }

                if (Str::endsWith($file, '.mp4')) {
                    $file_name = explode('-', $file);
                }
            }
            // Separo o nome do arquivo pelo '-'


            if ($jpg) {
                $this->line('Folder: ' . $folder . ' - ' . count($files) . ' files');
                var_dump('./storage/app/openvidu/' . $file_name[0] . '/' . $folder . '/' . $file);
                $fileAr = fopen('./storage/app/openvidu/' . $folder . '/' . $file, 'r+');
                $put = Storage::disk('s3')->put('openvidu/' . $file_name[0] . '/' . $folder . '/' . $file, $fileAr);
            }
    
            if ($put == true) {
                $this->info('File moved to S3');
                // Remove o arquivo do diretório local
                Storage::disk('local')->deleteDirectory('openvidu/' . $folder);
            } else {
                $this->error('File not moved to S3');
            }
        }   

        $this->info('Fixing the openvidu folder finished');
    }
}
