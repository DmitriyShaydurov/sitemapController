<?php
/**
 * Description: This class makes sitemaps
 * Could be used for creation of sitemaps over 50 000 URLs
 * Author: Dmitriy Shaydurov <dmitriy.shaydurov@gmail.com>
 * Author URI: http://smb-studio.com
 * Version: 1.0
 * Date: 30.04.2020
 * OpenCart version 1.5.6.3
 **/
use shaydurov\opencart\UrlCorrector;

class ControllerModuleShdSitemapGenerator extends Controller {

    use shaydurov\opencart\PrintDie;
    use shaydurov\opencart\Info;

    // the maximum number of urls in a single file is 50 000.
    // the maximum size of sitemap file is 10MB so if you have long urls do not set $urlsLimit = 50000 !
    protected  $urlsLimit = 49500;

    protected $domainName = HTTPS_CATALOG; // change to HTTP_CATALOG if http is used
    protected $sitemapsFolder = DIR . 'sitemaps/';
    protected $sitemapName = 'sitemap';
    protected $firstLine = '<?xml version="1.0" encoding="UTF-8"?>';
    protected $sitemapCounter = 1;
    protected $pagesQauntaty = 0;


	public function index()
    {
        $this->language->load('module/shd_sitemap_generator');
		$this->document->setTitle($this->language->get('heading_title'));
        $this->data['heading_title'] = $this->language->get('heading_title');


        $this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/shd_sitemap_generator', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('module/shd_sitemap_generator', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['button_cancel'] = $this->language->get('button_cancel');
        $this->data['token'] =  $this->session->data['token'];

        $info = $this-> getSitemapInfo();
        $this->data['time'] =   $this->language->get('info_execution_time_text') .  number_format($info['time'], 2, ',', ' ') .  $this->language->get('info_minutes');
        $this->data['page_quantaty'] =  number_format($info['page_quantaty'], 0, ',', ' ');
        $this->data['info'] = $info['info'];
        $this->data['date_modified'] =  $info['date_modified'];
        $this->data['correct_urls'] =  $info['correct_urls'];
        $this->data['url_warnings'] = $info['warnings'];

        $this->data['correct_urls_warning'] = ($info['correct_urls'] > 0) ? true : false;

        $warning = false;
        foreach ($info['info'] as $info) {
            if ($info['warning'] == 1) {
                $warning = true;
            }
        }

        $this->data['warning'] = $warning;


        if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

        $this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->template = 'module/shd_sitemap_generator.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
    }

    function makeSitemap()
    {
        $start = microtime(true);
        ini_set("max_execution_time", "0");

        // make visible all $products
        $this->load->model('module/shd_show_all_products');
        $this->model_module_shd_show_all_products->showAll();

        // correct urls
        $corrector = new UrlCorrector();
        $correctUrls = $corrector->correctSeoUrls(true);

        $this->cleanSitemapsFolder();
        //categories
        $this->makePageSitemap('category_id', DB_PREFIX .'category', 'monthly', '0.8', true);
        //products
        $this->makePageSitemap('product_id', DB_PREFIX .'product', 'weekly', '0.6', false);
        //index
        $this->makeIndexSitemap();
        // save all info
        $this->setSitemapInfo((microtime(true)-$start)/60, $correctUrls);
    }

    protected function makePageSitemap($pageType, $tableName, $changefreq, $priority, $insertDomainName)
    {

        $this->load->model('module/shd_sitemap_generator');

        $offset = 0;
        $continue = true;
        $i = 0;
            do {
                $products = $this->model_module_shd_sitemap_generator->PrepareUrlArray($pageType, $tableName, $changefreq, $priority,  $offset . ', ' . $this->urlsLimit, $insertDomainName);
                if ($products) {
                    $products = $this->sortArray($products);
                    $this->SingleSitemap($products, $this->sitemapsFolder . $this->sitemapName . $this->sitemapCounter . ".xml");
                    $offset = $offset + $this->urlsLimit;
                    $this->sitemapCounter++;
                    $i++;
                    $this->pagesQauntaty = $this->pagesQauntaty + count($products);
                } else {
                    $continue = false;
                }
            } while ($continue);
    }

    protected function SingleSitemap($urlArray, $sitemapName)
    {
        $currentFile = fopen($sitemapName, "w") or die("Unable to open file!");
        $txt = $this->firstLine;
        fwrite($currentFile, $txt . PHP_EOL);
        $txt = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        fwrite($currentFile, $txt . PHP_EOL);

        foreach ($urlArray as $line) {

            $lastMod = '';
            $changefreq = '';
            $priority = '';

            if (array_key_exists('lastmod', $line)) {
                $lastMod = '<lastmod>' . $line['lastmod'] . '</lastmod>' . PHP_EOL;
            }

            if (array_key_exists('changefreq', $line)) {
                $changefreq = '<changefreq>' . $line['changefreq'] . '</changefreq>' . PHP_EOL;
            }

            if (array_key_exists('priority', $line)) {
                $priority = '<priority>' . $line['priority'] . '</priority>' . PHP_EOL;
            }

            $txt = '<url>' . PHP_EOL . '<loc>' . $this->domainName . $line['url'] . '</loc>' . PHP_EOL .
                $lastMod . $changefreq . $priority . '</url>' . PHP_EOL;

            fwrite($currentFile, $txt);
        }

        $txt = '</urlset>';
        fwrite($currentFile, $txt . PHP_EOL);
        fclose($currentFile);
    }

     protected function sortArray($data)
     {
         usort($data, function ($a, $b) {
             if ($a['lastmod'] == $b['lastmod']) return 0;
             return (strtotime($a['lastmod']) < strtotime($b['lastmod'])) ? 1 : -1;
         });
         return $data;
     }

     protected function cleanSitemapsFolder()
     {
         array_map("unlink", glob($this->sitemapsFolder . $this->sitemapName . '*.xml'));
     }

     public function makeSitemapInfo()
     {
         $fileNames = glob($this->sitemapsFolder . "*.xml");
         if (!$fileNames) {
            return false;
         }

         $tobeReplaced = [$this->sitemapsFolder . $this->sitemapName, ".xml"];
         $replacement = ['', ''];

         foreach ($fileNames as $filename) {
             $number =  (int)str_replace($tobeReplaced, $replacement, $filename);
             $indexSitemapArray[$number]['size'] = round(filesize($filename)/1000000, 2);
             $indexSitemapArray[$number]['abs_name'] = $filename;
             $indexSitemapArray[$number]['name'] = $this->domainName . str_replace(DIR, '', $filename);
             $indexSitemapArray[$number]['lastmod'] = date('Y-m-d', filectime($filename));
             $indexSitemapArray[$number]['lastmod_accurate'] = date('d-m-Y h:i:sa', filectime($filename));

             // file size more than 10 MB is not allawed
             ($indexSitemapArray[$number]['size'] > 10 ) ? $indexSitemapArray[$number]['warning'] = 1 : $indexSitemapArray[$number]['warning'] = 0;
        }

        ksort($indexSitemapArray);
        return $indexSitemapArray;
     }

     protected function makeIndexSitemap()
     {
         $info = $this->makeSitemapInfo();
         if ($info) {
             $fName = $this->sitemapsFolder . $this->sitemapName . ".xml";
             $currentFile = fopen($fName, "w") or die("Unable to open file!");
             $txt = $this->firstLine;
             fwrite($currentFile, $txt . PHP_EOL);
             $txt = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
             fwrite($currentFile, $txt . PHP_EOL);

             foreach ($info as $line) {
                 $txt = '<sitemap>' . PHP_EOL . '<loc>' . $this->domainName . '/'. $this->AbsPathToSitemap . $line['name'] . '</loc>' . PHP_EOL .
                     '<lastmod>' . $line['lastmod'] . '</lastmod>' . PHP_EOL .
                     '</sitemap>' . PHP_EOL;
                 fwrite($currentFile, $txt);
             }
             $txt = '</sitemapindex>';
             fwrite($currentFile, $txt);
         }
     }

     protected function setSitemapInfo($timeElapsed, $correctUrls)
     {
          $SitemapInfo = [  'time'           => $timeElapsed,
                            'page_quantaty'  => $this->pagesQauntaty,
                            'info'           => $this->makeSitemapInfo(),
                            'date_modified'  => date("d-m-Y h:i:sa"),
                            'correct_urls'  => $correctUrls['urls_corrected'],
                            'warnings'       =>$correctUrls['warnings']];

          $this->set('sitemap_info', $SitemapInfo);
     }

     public function getSitemapInfo()
     {
        return $this->get('sitemap_info');
     }
}
