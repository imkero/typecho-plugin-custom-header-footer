<?php
namespace TypechoPlugin\CustomHeaderFooter;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Layout;
use Typecho\Widget\Helper\Form\Element;
use Typecho\Widget\Helper\Form\Element\Textarea;
use Widget\Options;
use Widget\User;

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
        $description = new PluginDescription('description', NULL, NULL, '说明', NULL);
        $form->addInput($description);

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
        $variables = self::collectVariables();
        $html = Options::alloc()->plugin('CustomHeaderFooter')->headHtml;
        foreach ($variables as $key => $value) {
            // 变量格式: {VAR_NAME}
            $html = str_replace("{{$key}}", $value, $html);
        }
        echo $html;
    }

    /**
     * 渲染自定义 footer HTML
     */
    public static function renderFooterHtml()
    {
        $variables = self::collectVariables();
        $html = Options::alloc()->plugin('CustomHeaderFooter')->footerHtml;
        foreach ($variables as $key => $value) {
            // 变量格式: {VAR_NAME}
            $html = str_replace("{{$key}}", $value, $html);
        }
        echo $html;
    }

    public static function collectVariables() {
        User::alloc()->to($user);
        Options::alloc()->to($options);

        return [
            'SITE_URL' => $options->siteUrl,
            'THEME_URL' => $options->themeUrl,
            'USER_GROUP' => $user->group ?? 'null',
        ];
    }

    public const VARIABLE_DESCRIPTION = [
        'SITE_URL' => '站点 URL',
        'THEME_URL' => '主题目录 URL',
        'USER_GROUP' => '当前用户的用户组 (administrator, editor, contributor, subscriber, visitor, null)',
    ];
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

/**
 * 插件设置页面头部说明
 */
class PluginDescription extends Element
{
    /**
     * 初始化当前输入项
     *
     * @param string|null $name 表单元素名称
     * @param array|null $options 选择项
     * @return Layout|null
     */
    public function input(?string $name = null, ?array $options = null): ?Layout
    {
        $p1 = new Layout('p');
        $p1->html('本插件支持在 &lt;head&gt; 标签内和 &lt;/body&gt; 标签之前插入自定义 HTML (对应主题代码中的 header() 和 footer() 调用)');
        $this->container($p1);

        $p2 = new Layout('p');
        $p2->html('在 HTML 代码中支持代入以下变量:');
        $this->container($p2);

        $ul = new Layout('ul');
        foreach (Plugin::collectVariables() as $key => $value) {
            $key = htmlspecialchars($key);
            $value = htmlspecialchars($value);
            $varDesc = htmlspecialchars(Plugin::VARIABLE_DESCRIPTION[$key]);

            $li = new Layout('li');
            $li->html("<p><code>{{$key}}</code>: <code>{$value}</code><br><span style=\"color:#999;font-size:.9em\">{$varDesc}</span>");
            $ul->addItem($li);
        }
        $this->container($ul);
        
        $input = new Layout('input');
        return $input;
    }

    /**
     * 设置表单项默认值
     *
     * @param mixed $value 表单项默认值
     */
    protected function inputValue($value)
    {
        $this->input->html($value);
    }
}
