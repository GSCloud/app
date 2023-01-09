<?php
/**
 * GSC Tesseract
 * php version 8.2
 *
 * @category Framework
 * @package  Tesseract
 * @author   Fred Brooker <git@gscloud.cz>
 * @license  MIT https://gscloud.cz/LICENSE
 * @link     https://app.gscloud.cz
 */

namespace GSC;

use Cake\Cache\Cache;
use Michelf\MarkdownExtra;

/**
 * App Presenter
 *
 * @category Framework
 * @package  Tesseract
 * @author   Fred Brooker <git@gscloud.cz>
 * @license  MIT https://gscloud.cz/LICENSE
 * @link     https://app.gscloud.cz
 */
class AppPresenter extends APresenter
{
    /**
     * Main controller
     * 
     * @param mixed $param optional parameter
     *
     * @return object Controller
     */
    public function process($param = null)
    {
        // setup
        $data = $this->getData();
        $presenter = $this->getPresenter();
        $view = $this->getView();
        $this->checkRateLimit()->setHeaderHtml()->dataExpander($data);

        // process template
        $template = 'app';
        if (\is_string($view) && \is_array($presenter)) {
            $template = array_key_exists("template", $presenter[$view])
                ? $presenter[$view]["template"] : 'app';
        }

        // process output
        $output = $this->setData($data)->renderHTML($template);
        StringFilters::trim_html_comment($output);
        return $this->setData("output", $output);
    }
}
