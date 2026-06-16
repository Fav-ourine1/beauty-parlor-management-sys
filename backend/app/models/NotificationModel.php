<?php
class NotificationModel extends Model
{
    protected string $table = 'notifications';

    public function getTemplate(string $code): array|false
    {
        return $this->db->fetchOne(
            'SELECT * FROM notification_templates WHERE code = ? AND is_active = 1 LIMIT 1',
            [$code]
        );
    }

    public function renderTemplate(array $template, array $vars): string
    {
        $body = $template['body'];

        foreach ($vars as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }

        return $body;
    }

    public function queue(
        string $templateCode,
        int|null $recipientUserId,
        string $recipientAddress,
        string $channel,
        array $vars
    ): string {
        $template = $this->getTemplate($templateCode);

        if (!$template) {
            throw new \RuntimeException("Notification template '{$templateCode}' not found or inactive.");
        }

        $body = $this->renderTemplate($template, $vars);

        $subject = null;
        if ($channel === 'email' && !empty($template['subject'])) {
            $subject = $template['subject'];
            foreach ($vars as $key => $value) {
                $subject = str_replace('{{' . $key . '}}', $value, $subject);
            }
        }

        return $this->db->insert(
            'INSERT INTO notifications (template_id, recipient_user_id, channel, recipient_address, subject, body, status)
             VALUES (?, ?, ?, ?, ?, ?, \'queued\')',
            [$template['id'], $recipientUserId, $channel, $recipientAddress, $subject, $body]
        );
    }

    public function getPending(int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM notifications WHERE status = 'queued' ORDER BY created_at LIMIT ?",
            [$limit]
        );
    }

    public function markSent(int $id, ?string $providerMessageId = null): int
    {
        return $this->db->execute(
            "UPDATE notifications SET status = 'sent', sent_at = NOW(), provider_message_id = ? WHERE id = ?",
            [$providerMessageId, $id]
        );
    }

    public function markFailed(int $id): int
    {
        return $this->db->execute(
            "UPDATE notifications SET status = 'failed' WHERE id = ?",
            [$id]
        );
    }

    public function getForUser(int $userId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM notifications WHERE recipient_user_id = ? ORDER BY created_at DESC LIMIT ?',
            [$userId, $limit]
        );
    }
}
