<?php 

/**
 * AkismetClient for using the Akismet API to protect yourself from spam.
 * Visit Akismet at <http://akismet.com/>.
 * 
 * You will need an Akismet API key.
 * If checking a comment for spam or submitting spam or ham, the client's IP adress, user agent and referer will be submitted to Akismet.
 * 
 * @author Sebastian Herbermann
 * @license The MIT License <http://www.opensource.org/licenses/mit-license.php>
 */
class AkismetClient
{
	private $apiKey, $blogUrl;
	
	private $extendedPrivacy = false;

	/**
	 * Constructor
	 * @param String $apiKey Your API key.
	 * @param String $blogUrl The absolute URL to your Blog (Frontpage).
	 */
	public function __construct($apiKey, $blogUrl) {
		$this->apiKey = $apiKey;
		$this->blogUrl = $blogUrl;
	}

	/**
	 * Checks if your supplied API key is valid. If not, a exception is thrown.
	 * @throws AkismetClientException
	 */
	private function verifyKey() {
		$res = $this->makeCall(
				'http://rest.akismet.com/1.1/verify-key', 
				array(
						'key' => $this->apiKey, 
						'blog' => $this->blogUrl
		));
		
		if ($res != 'valid') {
			throw new AkismetClientException('Invalid key');
		}
	}
	
	/**
	 * Check if a comment is spam. Returns true if spam, false if not. You can use an empty string for any parameter.
	 * @param String $permalink The (perma-)link to your post.
	 * @param String $commentType The type of the comment. May be blank, "comment", "trackback", "pingback", or a made up value like "registration".
	 * @param String $commentAuthor The name of the author.
	 * @param String $commentAuthorEmail The author's email.
	 * @param String $commentAuthorUrl The author's homepage.
	 * @param String $commentContent The content of the comment.
	 * @throws AkismetClientException
	 */
	public function checkComment($permalink = '', $commentType = '', $commentAuthor = '', $commentAuthorEmail = '', $commentAuthorUrl = '', $commentContent = '') {
		$this->verifyKey();
		$res = $this->makeCall(
				sprintf('http://%s.rest.akismet.com/1.1/comment-check', $this->apiKey),
				array(
						'blog' => $this->blogUrl,
						'user_ip' => self::getHeader('REMOTE_ADDR'),
						'user_agent' => self::getHeader('HTTP_USER_AGENT'),
						'referer' => self::getHeader('HTTP_REFERER'),
						
						'permalink' => $permalink,
						'comment_type' => $commentType,
						'comment_author' => $commentAuthor,
						'comment_author_email' => $commentAuthorEmail,
						'comment_author_url' => $commentAuthorUrl,
						'comment_content' => $commentContent
		));
		
		if ($res == 'invalid') {
			throw new AkismetClientException('Error checking comment.');
		}
		
		return $res == 'true';
	}

	/**
	 * Submit spam. You can use an empty string for any parameter.
	 * @param String $permalink The (perma-)link to your post.
	 * @param String $commentType The type of the comment. May be blank, "comment", "trackback", "pingback", or a made up value like "registration".
	 * @param String $commentAuthor The name of the author.
	 * @param String $commentAuthorEmail The author's email.
	 * @param String $commentAuthorUrl The author's homepage.
	 * @param String $commentContent The content of the comment.
	 * @throws AkismetClientException
	 */
	public function submitSpam($permalink = '', $commentType = '', $commentAuthor = '', $commentAuthorEmail = '', $commentAuthorUrl = '', $commentContent = '') {
		$this->verifyKey();
		$res = $this->makeCall(
				sprintf('http://%s.rest.akismet.com/1.1/submit-spam', $this->apiKey),
				array(
						'blog' => $this->blogUrl,
						'user_ip' => self::getHeader('REMOTE_ADDR'),
						'user_agent' => self::getHeader('HTTP_USER_AGENT'),
						'referer' => self::getHeader('HTTP_REFERER'),
						
						'permalink' => $permalink,
						'comment_type' => $commentType,
						'comment_author' => $commentAuthor,
						'comment_author_email' => $commentAuthorEmail,
						'comment_author_url' => $commentAuthorUrl,
						'comment_content' => $commentContent
		));
		
		if ($res == 'invalid') {
			throw new AkismetClientException('Error submitting spam.');
		}
	}

	/**
	 * Submit ham. You can use an empty string for any parameter.
	 * @param String $permalink The (perma-)link to your post.
	 * @param String $commentType The type of the comment. May be blank, "comment", "trackback", "pingback", or a made up value like "registration".
	 * @param String $commentAuthor The name of the author.
	 * @param String $commentAuthorEmail The author's email.
	 * @param String $commentAuthorUrl The author's homepage.
	 * @param String $commentContent The content of the comment.
	 * @throws AkismetClientException
	 */
	public function submitHam($permalink = '', $commentType = '', $commentAuthor = '', $commentAuthorEmail = '', $commentAuthorUrl = '', $commentContent = '') {
		$this->verifyKey();
		$res = $this->makeCall(
				sprintf('http://%s.rest.akismet.com/1.1/submit-ham', $this->apiKey),
				array(
						'blog' => $this->blogUrl,
						'user_ip' => $this->getHeader('REMOTE_ADDR'),
						'user_agent' => $this->getHeader('HTTP_USER_AGENT'),
						'referer' => $this->getHeader('HTTP_REFERER'),
						
						'permalink' => $permalink,
						'comment_type' => $commentType,
						'comment_author' => $commentAuthor,
						'comment_author_email' => $commentAuthorEmail,
						'comment_author_url' => $commentAuthorUrl,
						'comment_content' => $commentContent
		));
		
		if ($res == 'invalid') {
			throw new AkismetClientException('Error submitting ham.');
		}
	}
	
	/**
	 * Get a value from the $_SERVER array or an empty string if not present.
	 * @param String $header
	 */
	private function getHeader($header) {
		if ($this->extendedPrivacy)
			return '';
			
		return (isset($_SERVER[$header]) ? $_SERVER[$header] : '');
	}

	/**
	 * Make a request to the Akismet server.
	 * @param String $url
	 * @param Array $params
	 */
	private function makeCall($url, $params) {
		$content = '';
		foreach ($params as $key => $value) {
			$content .= 
				(strlen($content) > 0 ? '&' : '')
				. $key .'='. urlencode($value);
		}
		
		$opts = array(
				'http' => array(
						'method' => 'POST',
						'user_agent '=> 'NeatBlog/2.0 | Akismet 1.0',
						'header' => 'Content-Type: application/x-www-form-urlencoded',
						'content' => $content
				)
		);
		$context = stream_context_create($opts);
		
		$res = file_get_contents($url, false, $context);
		return $res;
	}
	
	/**
	 * Enable the extended privacy mode. This prevents the user's IP address, user agent and referer from being submitted to Akismet.
	 * This may result in less accurate spam detection or failing requests to Akismet.
	 */
	public function enableExtendedPrivacy() {
		$this->extendedPrivacy = true;
	}
	
	/**
	 * Disbale the extended privacy mode.
	 */
	public function disableExtendedPrivacy() {
		$this->extendedPrivacy = false;
	}
}

class AkismetClientException extends Exception {};