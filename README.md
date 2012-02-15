# AkismetClient

A simple PHP Client for Akismet Anti-Spam. 
Visit Akismet at <http://akismet.com/>.


## Usage
 
You will need an Akismet API key.

~~~~~~~~~ { php }
<?php

try {
	/*
	 * Supply your API key and the URL to the homepage of your blog.
	 */
	$akismetClient = new AkismetClient($apiKey, 'http://www.example.com/');
	
	// Supply as much information as possible, but you do not have to supply everything.
	$comment = array(
		// The (perma-)link to your post.
		'permalink' => 'http://www.example.com/why-would-you-post-spam',
		// The type of the comment. May be blank, "comment", "trackback", 
		// "pingback", or a made up value like "registration".
		'comment_type' => 'comment',
		// The name of the author.
		'comment_author' => 'Bob', 
		// The author's email.
		'comment_author_email' => 'bob@example.com', 
		// The author's homepage.
		'comment_author_url' => 'http://bobs-website.example.com', 
		// The content of the comment.
		'comment_content' => 'This is the text of comment.' 
	);
	
	/*
	 * Check for spam.
	 * You can supply an empty string if you want to skip a parameter.
	 */
	if ($akismetClient->checkComment($comment)) {
		echo 'Spam found!';
	} else {
		echo 'Hurray! No spam!';
	}
} catch (AkismetClientException $e) {
	echo 'Something went wrong.';
}
~~~~~~~~~

You can also `submitSpam()` or `submitHam()`. The parameters are the same as in `checkComment()`.

Use `enableExtendedPrivacy()` if you do not want to send the users IP address, user agent and referer to Akismet.
This may break spam detection or raise other errors at the Akismet-API.

## License

The MIT License (MIT)

Copyright (c) 2012 Sebastian Herbermann

Permission is hereby granted, free of charge, to any person obtaining a 
copy of this software and associated documentation files (the "Software"), 
to deal in the Software without restriction, including without limitation 
the rights to use, copy, modify, merge, publish, distribute, sublicense, 
and/or sell copies of the Software, and to permit persons to whom the Software 
is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in 
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS 
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR 
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER 
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.