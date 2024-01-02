<?php
namespace TypechoPlugin\CustomHeaderFooter;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Layout;
use Typecho\Widget\Helper\Form\Element\Textarea;
use Widget\Options;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 在 &lt;head&gt; 标签内和 &lt;/body&gt; 标签之前插入自定义 HTML
 *
 * @package CustomHeaderFooter
 * @author imkero
 * @version 1.0.0
 * @link https://imkero.net
 */
class Plugin implements PluginInterface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
    public static function activate()
    {
        \Typecho\Plugin::factory('Widget\Archive')->header = __CLASS__ . '::renderHeadHtml';
        \Typecho\Plugin::factory('Widget\Archive')->footer = __CLASS__ . '::renderFooterHtml';
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate()
    {
    }

    /**
     * 获取插件配置面板
     *
     * @param Form $form 配置面板
     */
    public static function config(Form $form)
    {
        $headHtml = new MonospaceTextarea('headHtml', null, '', '&lt;head&gt; 标签内自定义 HTML');
        $form->addInput($headHtml);

        $footerHtml = new MonospaceTextarea('footerHtml', null, '', '&lt;/body&gt; 标签前自定义 HTML');
        $form->addInput($footerHtml);
    }

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Form $form)
    {
    }

    /**
     * 渲染自定义 head HTML
     */
    public static function renderHeadHtml()
    {
        echo Options::alloc()->plugin('CustomHeaderFooter')->headHtml;
    }

    /**
     * 渲染自定义 footer HTML
     */
    public static function renderFooterHtml()
    {
        echo Options::alloc()->plugin('CustomHeaderFooter')->footerHtml;
    }
}

/**
 * 用于在插件配置面板中显示等宽字体的 <textarea>
 */
class MonospaceTextarea extends Textarea {
    /**
     * 初始化当前输入项
     *
     * @param string|null $name 表单元素名称
     * @param array|null $options 选择项
     * @return Layout|null
     */
    public function input(?string $name = null, ?array $options = null): ?Layout
    {
        $layout = parent::input($name, $options);
        $layout->style = 'font-family: monospace';
        return $layout;
    }
}
