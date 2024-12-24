<?php
namespace App\Http\Middleware;

use Closure;

/**
 * 允许Ajax跨域
 *
 */
class AjaxAccessControl
{

	/**
	 * 允许该主域下的子域名跨域
	 */
    protected $primary_domains = ['.wlanbanlv.com', '.wifibanlv.com', '.wi-fi.cn', '.laidianbanlv.cn', '.hzjiuhuang.cn',
        '.ejnjyc.cn', '.zonelian.net', '.zhonglianhuashu.com', '.u10010.com', '.zonelian.com',];

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$response = $next($request);

		$allow_url = '';

		if (config('app.debug') ) {
            if($request->header('referer')) {
                // 调试模式允许所有来源。
                //$allow_url = '*';
                $parts = parse_url($request->header('referer'));
                $allow_url = $parts['scheme'] . '://' . $parts['host'];
                if (isset($parts['port'])) {
                    $allow_url .= ':'.$parts['port'];
                }
            }
		} elseif ($referer = $request->header('referer')) {
			// 解析来路URL。
			$parts = parse_url($referer);

            if (isset($parts['host']) && isset($parts['scheme'])) {
                foreach($this->primary_domains as $primary_domain) {
                    // 检查域名是否在主域下。
                    if (preg_match('/(.+)' . str_replace('.', '\\.', $primary_domain) . '(:[0-9]*)?' . '$/i', $parts['host'], $matches)) {
                        $allow_url = $parts['scheme'] . '://' . $matches[1] . $primary_domain;
                        if (isset($matches[2])) {
                            $allow_url .= $matches[2];
                        }
                        break;
                    }
                }

            }
		}

		if ($allow_url) {
			$response->headers->set('Access-Control-Allow-Origin', $allow_url);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');

        }

		return $response;
	}
}
