<?php
class Mail_Exception extends Exception { }

class Oxygen_Mail
{
    CONST PRIORITY_MAXIMAL = 1;
    CONST PRIORITY_UPPER   = 2;
    CONST PRIORITY_NORMAL  = 3;
    CONST PRIORITY_LOWER   = 4;
    CONST PRIORITY_MINIMAL = 5;

    public static function sendmail(
        $to,
        $subject,
        $message,
        $isHTML = true,
        $attachments = array(),
        $cc = array(),
        $bcc = array(),
        $priority = null,
        $replyto = null,
        $from = null,
        $additionalHeaders = array(),
        $charset = 'utf-8'
    )
    {
        $headers = array();
        $content = array();

        $line_feed = "\n";

        $config = Config::getInstance();

        $subject = $config->getOption('mailPrepend').$subject;

        if (empty($replyto))
            $replyto = $config->getOption('mailReplyTo');

        if (empty($from))
        {
            $from = $config->getOption('mailFrom');

            if(empty($from))
                throw new Mail_Exception('default \'from\' mail adress not found and none provided, please use config option \'mailFrom\'');
        }

        // Setting boundaries limiters
        $boundary     = "=_".md5(rand());
        $boundary_alt = "=_".md5(rand());

        // Headers
        $headers[] = "From: ".$from;

        if (!empty($replyto))
            $headers[] = "Reply-to: ".$replyto;

        $headers[] = "MIME-Version: 1.0";

        if (!empty($priority))
            $headers[] = "X-Priority: ".$priority;

        $mime = empty($attachments) ? 'multipart/alternative' : 'multipart/mixed';
        $headers[] = "Content-Type: $mime;"." boundary=\"$boundary\"";

        if (!empty($additionalHeaders))
            $headers = array_merge($headers, $additionalHeaders);

        // Handling message content
        $content[] = '';
        $content[] = "--".$boundary;

        if ($isHTML)
        {
            $content[] = "Content-Type: text/html; charset=\"$charset\"";
            $content[] = "Content-Transfer-Encoding: 8bit";
        }
        else
        {
            $content[] = "Content-Type: text/plain; charset=\"$charset\"";
            $content[] = "Content-Transfer-Encoding: 8bit";
        }

        $content[] = '';
        $content[] = $message;

        // Adding attachments
        if (!empty($attachments))
        {
            foreach($attachments as $mimetype => $filepath)
            {
                $file   = fopen($filepath, "r");
                $mimetype = (false !== strpos($mimetype, '/')) ? $mimetype : 'application/octet-stream';

                // File not found, dismiss
                if (empty($file))
                    continue;

                $attachement = @fread($file, filesize($filepath));
                fclose($file);

                // File empty, dismiss it
                if (empty($attachement))
                    continue;

                $attachement = chunk_split(base64_encode($attachement));

                $filename = basename($filepath);

                // Add attachment into content
                $content[] = '';
                $content[] = "--".$boundary;
                $content[] = "Content-Type: $mimetype; name=\"$filename\"";
                $content[] = "Content-Transfer-Encoding: base64";
                $content[] = "Content-Disposition: attachment; filename=\"$filename\"";
                $content[] = '';
                $content[] = $attachement;
            }
        }

        $content[] = '';
        $content[] = "--".$boundary."--";
        $content[] = '';

        // Sending the mail
        return mail(
            $to,
            $subject,
            implode($line_feed, $content),
            implode($line_feed, $headers)
        );
    }
}