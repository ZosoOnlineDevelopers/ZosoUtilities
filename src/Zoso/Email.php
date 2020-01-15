<?php
namespace Zoso;

use Cake\Network\Http\Client;
use Cake\Log\Log;
use Postmark\Models\PostmarkAttachment;
use Postmark\PostmarkClient;
use Postmark\Models\PostmarkException;

class Email
{
    /**
     * Static test function
     *
     * @return String "Hello World, Composer!"
     */
    public static function test()
    {
        return 'Hello World, Composer!';
    }
    

    protected $client = 'Postmark';
    
    //Default API key
    protected $apiKey = 'ef2577e0-7ca0-4e0f-89cf-35214f05033c';
    
    protected $subject;
        
    protected $from = 'mail@zoso.nl';
    
    protected $fromName;
    
    protected $to;
    
    protected $toName;
    
    protected $replyTo;
    
    protected $attachments;
    
    protected $template;
    
    protected $templateVars = [];
    
    protected $subaccount;
    
    /**
     * Provide a client
     *
     * @param String $client
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }
    
    /**
     * Provide an API key
     *
     * @param String $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }
    
    /**
     * Provide a subject, also set it in the templateVars array
     *
     * @param String $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        
        $this->templateVars['subject'] = $subject;
        return $this;
    }
    
    /**
     * Provide a sender e-mailaddress
     *
     * @param String $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }
    
    /**
     * Provide a sender name
     *
     * @param String $fromName
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
        return $this;
    }
    
    /**
     * Provide a recipient e-mailaddress
     *
     * @param String $to
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }
    
    /**
     * Provide a recipient name
     *
     * @param String $toName
     */
    public function setToName($toName)
    {
        $this->toName = $toName;
        return $this;
    }
    
    /**
     * Provide a reply to e-mailaddress
     *
     * @param String $replyTo
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;
        return $this;
    }
    
    /**
     * Provide an array of attachments
     * The format of the array has to be filename => filepath
     *
     * @param Array $attachments
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
        return $this;
    }
    
    /**
     * Provide a template name or ID (depending on client)
     *
     * @param String $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }
    
    /**
     * Provide values for the template
     * If the subject was already set, make sure it is not overwritten
     *
     * @param String $templateVars
     */
    public function setTemplateVars($templateVars)
    {
        $this->templateVars = $templateVars;
        
        if (!empty($this->subject)) {
            $this->templateVars['subject'] = $this->subject;
        }
        
        return $this;
    }
    
    /**
     * Provide a subaccount for Mandrill
     *
     * @param String $subaccount
     */
    public function setSubaccount($subaccount)
    {
        $this->subaccount = $subaccount;
        return $this;
    }
    
    /**
     * Send a mail based on the client that is set.
     *
     */
    public function sendMail()
    {
        switch ($this->client) {
            default:
            case 'Postmark':
                $result = $this->postmark();
                break;
            case 'Mandrill':
                $result = $this->mandrill();
                break;
        }
        return $result;
    }
    
    /**
     * Send a mail using Postmark service
     *
     */
    public function postmark()
    {
        try {
            $attachments = [];
            if (!empty($this->attachments)) {
                foreach ($this->attachments as $filename => $path) {
                    $attachments[] = PostmarkAttachment::fromFile($path, $filename, mime_content_type($path));
                }
            }
            
            if (!empty($this->toName)) {
                $to = $this->toName.' <'.$this->to.'>';
            } else {
                $to = $this->to;
            }
            if (!empty($this->fromName)) {
                $from = $this->fromName.' <'.$this->from.'>';
            } else {
                $from = $this->from;
            }
            
            $client = new PostmarkClient($this->apiKey);
            $sendResult = $client->sendEmailWithTemplate(
                $from,
                $to, 
                $this->template, 
                $this->templateVars, 
                true, //inlineCss
                NULL, //tag
                true, //trackOpens
                $this->replyTo,
                NULL, //cc
                NULL, //bcc
                NULL, //headers
                $attachments,
                NULL, //trackLinks
                NULL //metadata
            );
            
            return $sendResult;
        } catch (PostmarkException $ex) {
            // If client is able to communicate with the API in a timely fashion,
            // but the message data is invalid, or there's a server error,
            // a PostmarkException can be thrown.
            //Loggen
            Log::info('Status '.$ex->httpStatusCode.': API error code: '.$ex->postmarkApiErrorCode.' Message: '.$ex->message, 'mail');
        } catch (\Exception $generalException) {
            // A general exception is thrown if the API
            // was unreachable or times out.
            //Loggen
            Log::info('Error: '.$generalException->getMessage(), 'mail');
        }
    }
    
    /**
     * Send a mail using Mandrill service
     * Set 'bericht' in the templateVars for the content
     * Note: no further validation is being done
     *
     */
    public function mandrill()
    {
        $template_content = [];
        foreach ($this->templateVars as $index => $value) {
            $template_content[] = [
                'name' => $index,
                'content' => trim(preg_replace('/\s+/', ' ', ($value)))
            ];
        }
        
        $attachments = [];
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $filename => $path) {
                $attachments[] = [
                    'type' => mime_content_type($path),
                    'name' => $filename,
                    'content' => base64_encode(file_get_contents($path))
                ];
            }
        }
        
        $content = '';
        if (isset($this->templateVars['bericht'])) {
            $content = $this->templateVars['bericht'];
        }
        $content_text = strip_tags($content);
        
        $uri = 'https://mandrillapp.com/api/1.0/messages/send-template.json';
        $params = [
            "key" => $this->apiKey,
            "template_name" => $this->template,
            "template_content" => $template_content,
            "message" => [
                "html" => $content,
                "text" => $content_text,
                "subject" => $this->subject,
                "from_email" => $this->from,
                "from_name" => $this->fromName,
                "to" => [
                    [
                        "name" => $this->toName, 
                        "email" => $this->to
                    ]
                ],
                "headers" => [
                    "Reply-To" => $this->replyTo,
                ],
                "track_opens" => true,
                "track_clicks" => true,
                "auto_text" => true,
                "url_strip_qs" => true,
                "preserve_recipients" => true,
                "subaccount" => $this->subaccount,
                "attachments" => $attachments
            ],
            "async" => false
        ];
        $postString = json_encode($params);
        
        $http = new Client();
        $response = $http->post($uri, $postString);
        
        return $response;
    }
    
}
