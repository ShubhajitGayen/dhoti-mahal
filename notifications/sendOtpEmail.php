<?php

function sendOtpEmail(string $toEmail, string $toName, string $otp): bool
{
    // Import inside the function so autoload is guaranteed to have run first
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // ── Server settings ─────────────────────────────────────
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'iiecems@gmail.com';
        $mail->Password   = 'giyu wskf mfnl bcwi';
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 30; // seconds — prevents silent hang on slow connections

        // ── Addresses ───────────────────────────────────────────
        $mail->setFrom('iiecems@gmail.com', SITE_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo('iiecems@gmail.com', SITE_NAME);

        // ── Content ─────────────────────────────────────────────
        $mail->isHTML(true);
        $mail->Subject = 'Your ' . SITE_NAME . ' Password Reset Code';

        // HTML body — styled to match the Dhoti Mahal brand palette
        $mail->Body = '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f5f0e8;font-family:Georgia,serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f0e8;padding:40px 0;">
    <tr><td align="center">
      <table width="480" cellpadding="0" cellspacing="0"
             style="background:#fdfaf5;border-radius:12px;overflow:hidden;border:1px solid #e0d5c5;">

        <!-- Header bar -->
        <tr>
          <td style="background:#1a1008;padding:28px 36px;">
            <p style="margin:0;font-family:Georgia,serif;font-size:22px;color:#f5e6c4;letter-spacing:0.02em;">
              Dhoti Mahal<span style="color:#d4a03c;">.</span>
            </p>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:36px 36px 28px;">
            <p style="margin:0 0 8px;font-family:Georgia,serif;font-size:20px;color:#1a1008;font-weight:normal;">
              Password reset request
            </p>
            <p style="margin:0 0 24px;font-size:14px;color:#8a7a65;font-family:Arial,sans-serif;line-height:1.6;">
              Hi ' . htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') . ',<br>
              Use the code below to reset your ' . htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') . ' account password.
              This code expires in <strong style="color:#5a4a38;">15 minutes</strong>.
            </p>

            <!-- OTP box -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
              <tr>
                <td align="center"
                    style="background:#1a1008;border-radius:10px;padding:22px 16px;">
                  <p style="margin:0 0 6px;font-size:10px;letter-spacing:2px;text-transform:uppercase;
                             color:rgba(245,230,196,0.5);font-family:Arial,sans-serif;">
                    Your reset code
                  </p>
                  <p style="margin:0;font-size:38px;font-weight:bold;letter-spacing:14px;
                             color:#d4a03c;font-family:\'Courier New\',monospace;padding-left:14px;">
                    ' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '
                  </p>
                </td>
              </tr>
            </table>

            <p style="margin:0 0 8px;font-size:13px;color:#a09080;font-family:Arial,sans-serif;line-height:1.6;">
              If you did not request a password reset, you can safely ignore this email —
              your password will not change.
            </p>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f5f0e8;padding:18px 36px;border-top:1px solid #e0d5c5;">
            <p style="margin:0;font-size:11px;color:#b0a090;font-family:Arial,sans-serif;">
              &copy; ' . date('Y') . ' ' . htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') . '. All rights reserved.
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';

        // Plain-text fallback
        $mail->AltBody = "Hi {$toName},\n\n"
            . "Your " . SITE_NAME . " password reset code is:\n\n"
            . "    {$otp}\n\n"
            . "This code expires in 15 minutes.\n\n"
            . "If you did not request this, ignore this email.\n\n"
            . "— " . SITE_NAME . " Team";


        $mail->send();
        return true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log('[Dhoti Mahal] PHPMailer error for ' . $toEmail . ': ' . $mail->ErrorInfo);
        // Store error in session so the dev OTP banner also shows the reason
        $_SESSION['fp_mail_error'] = $mail->ErrorInfo;
        return false;
    }
}
