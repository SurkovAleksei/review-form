<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function sendOwnerEmail(array $data, array $analysis): void
    {
        try {
            $ownerEmail = config('mail.owner_email', env('OWNER_EMAIL', 'owner@example.com'));

            $subject = 'New contact form submission';
            if (isset($analysis['urgency']) && $analysis['urgency'] >= 4) {
                $subject = 'URGENT! ' . $subject;
            }

            $html = $this->buildOwnerEmailHtml($data, $analysis);

            Mail::html($html, function ($message) use ($ownerEmail, $subject) {
                $message->to($ownerEmail)
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->subject($subject);
            });

            Log::info('Owner email sent', ['email' => $ownerEmail]);

        } catch (\Exception $e) {
            Log::error('Failed to send owner email', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function sendUserEmail(array $data, array $analysis): void
    {
        try {
            $html = $this->buildUserEmailHtml($data, $analysis);

            Mail::html($html, function ($message) use ($data) {
                $message->to($data['email'])
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->subject('Thank you for your message!');
            });

            Log::info('User copy email sent', ['email' => $data['email']]);

        } catch (\Exception $e) {
            Log::error('Failed to send user email', [
                'error' => $e->getMessage(),
                'email' => $data['email']
            ]);
        }
    }

    private function buildOwnerEmailHtml(array $data, array $analysis): string
    {
        $categories = [
            'question' => 'Question',
            'proposal' => 'Proposal',
            'complaint' => 'Complaint',
            'other' => 'Other'
        ];

        $sentiments = [
            'positive' => 'Positive',
            'neutral' => 'Neutral',
            'negative' => 'Negative'
        ];

        $category = $categories[$analysis['category'] ?? 'other'] ?? 'Other';
        $sentiment = $sentiments[$analysis['sentiment'] ?? 'neutral'] ?? 'Neutral';
        $sentimentScore = $analysis['sentiment_score'] ?? 5;
        $urgency = $analysis['urgency'] ?? 3;
        $keyTopics = $analysis['key_topics'] ?? [];
        $autoReply = $analysis['auto_reply'] ?? '';
        $aiUsed = $analysis['ai_used'] ?? false;
        $receivedAt = $data['received_at'] ?? date('d.m.Y H:i:s');
        $phone = $data['phone'] ?? 'Not specified';
        $appName = config('app.name', 'App');
        $currentYear = date('Y');

        $topicsHtml = '';
        if (!empty($keyTopics)) {
            $topicsHtml = '<div style="margin-top: 10px; font-size: 14px;"><strong>Topics:</strong> ' . implode(', ', $keyTopics) . '</div>';
        }

        $replyHtml = '';
        if (!empty($autoReply)) {
            $replyHtml = '
            <div class="section">
                <div class="section-title">Generated Response</div>
                <div class="reply-box">' . nl2br(htmlspecialchars($autoReply)) . '</div>
            </div>';
        }

        $aiBadgeClass = $aiUsed ? 'used' : 'fallback';
        $aiBadgeText = $aiUsed ? 'AI used' : 'Fallback mode';
        $urgencyClass = $urgency >= 4 ? 'urgency-high' : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Form Submission</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; background: #f8f9fa; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .header { border-bottom: 2px solid #4a90d9; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #1a1a2e; font-size: 24px; margin: 0; }
        .header .meta { color: #666; font-size: 14px; margin-top: 8px; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 16px; font-weight: 600; color: #4a90d9; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 8px; }
        .field { display: flex; margin-bottom: 8px; }
        .field-label { font-weight: 500; color: #555; width: 100px; flex-shrink: 0; }
        .field-value { color: #1a1a2e; }
        .comment-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 5px; border-left: 3px solid #4a90d9; }
        .analysis-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; }
        .analysis-item { background: #f8f9fa; padding: 10px 15px; border-radius: 6px; }
        .analysis-item .label { font-size: 12px; color: #999; text-transform: uppercase; display: block; }
        .analysis-item .value { font-size: 16px; font-weight: 500; color: #1a1a2e; }
        .reply-box { background: #f0f7ff; padding: 15px 20px; border-radius: 8px; border-left: 3px solid #4a90d9; margin-top: 10px; white-space: pre-wrap; }
        .ai-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .ai-badge.used { background: #d4edda; color: #155724; }
        .ai-badge.fallback { background: #fff3cd; color: #856404; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 13px; text-align: center; }
        .urgency-high { color: #dc3545; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Contact Form Submission</h1>
            <div class="meta">Received: {$receivedAt}</div>
        </div>

        <div class="section">
            <div class="section-title">Contact Details</div>
            <div class="field"><span class="field-label">Name:</span><span class="field-value">{$data['name']}</span></div>
            <div class="field"><span class="field-label">Email:</span><span class="field-value">{$data['email']}</span></div>
            <div class="field"><span class="field-label">Phone:</span><span class="field-value">{$phone}</span></div>
        </div>

        <div class="section">
            <div class="section-title">Message</div>
            <div class="comment-box">{$data['comment']}</div>
        </div>

        <div class="section">
            <div class="section-title">AI Analysis</div>
            <div style="margin-bottom: 10px;">
                <span class="ai-badge {$aiBadgeClass}">{$aiBadgeText}</span>
            </div>
            <div class="analysis-grid">
                <div class="analysis-item">
                    <span class="label">Category</span>
                    <span class="value">{$category}</span>
                </div>
                <div class="analysis-item">
                    <span class="label">Sentiment</span>
                    <span class="value">{$sentiment}</span>
                </div>
                <div class="analysis-item">
                    <span class="label">Score</span>
                    <span class="value">{$sentimentScore}/10</span>
                </div>
                <div class="analysis-item">
                    <span class="label">Urgency</span>
                    <span class="value {$urgencyClass}">{$urgency}/5</span>
                </div>
            </div>
            {$topicsHtml}
        </div>

        {$replyHtml}

        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center;">
            <a href="mailto:{$data['email']}" style="display: inline-block; padding: 10px 25px; background: #4a90d9; color: white; text-decoration: none; border-radius: 6px; margin-right: 10px;">Reply</a>
        </div>

        <div class="footer">
            This email was generated automatically.<br>
            {$appName} &copy; {$currentYear}
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function buildUserEmailHtml(array $data, array $analysis): string
    {
        $categories = [
            'question' => 'Question',
            'proposal' => 'Proposal',
            'complaint' => 'Complaint',
            'other' => 'Other'
        ];

        $sentiments = [
            'positive' => 'Positive',
            'neutral' => 'Neutral',
            'negative' => 'Negative'
        ];

        $category = $categories[$analysis['category'] ?? 'other'] ?? 'Other';
        $sentiment = $sentiments[$analysis['sentiment'] ?? 'neutral'] ?? 'Neutral';
        $sentimentScore = $analysis['sentiment_score'] ?? 5;
        $autoReply = $analysis['auto_reply'] ?? 'Thank you for your message. I will get back to you soon.';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Your Message</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; background: #f8f9fa; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .header { text-align: center; border-bottom: 2px solid #4a90d9; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #1a1a2e; font-size: 24px; margin: 0 0 8px 0; }
        .header p { color: #666; margin: 0; font-size: 14px; }
        .greeting { font-size: 20px; color: #1a1a2e; margin-bottom: 15px; }
        .reply-box { background: #f0f7ff; padding: 15px 20px; border-radius: 8px; border-left: 3px solid #4a90d9; white-space: pre-wrap; font-size: 15px; line-height: 1.8; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px; display: flex; gap: 20px; flex-wrap: wrap; }
        .info-item { flex: 1; min-width: 120px; }
        .info-item .label { font-size: 12px; color: #999; text-transform: uppercase; display: block; }
        .info-item .value { font-size: 15px; font-weight: 500; color: #1a1a2e; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 13px; text-align: center; }
        .btn { display: inline-block; padding: 12px 30px; background: #4a90d9; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .btn:hover { background: #357abd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Thank You for Your Message</h1>
            <p>We received your message and will respond shortly</p>
        </div>

        <div class="greeting">Hello, {$data['name']}!</div>

        <p style="color: #666; margin-bottom: 20px;">
            Thank you for reaching out. I have reviewed your request and prepared a preliminary response:
        </p>

        <div class="reply-box">{$autoReply}</div>

        <div class="info-box">
            <div class="info-item">
                <span class="label">Request Type</span>
                <span class="value">{$category}</span>
            </div>
            <div class="info-item">
                <span class="label">Sentiment</span>
                <span class="value">{$sentiment}</span>
            </div>
            <div class="info-item">
                <span class="label">Score</span>
                <span class="value">{$sentimentScore}/10</span>
            </div>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="https://github.com/SurkovAleksei" class="btn">Visit Website</a>
        </div>

        <div class="footer">
            Best regards,<br>
            <strong>Developer</strong><br>
            <span style="font-size: 12px; color: #999;">This email was generated automatically.</span>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
