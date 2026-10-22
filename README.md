# Paniz Mani Studios

A photography portfolio website featuring the artist's work, a browsable gallery, and a service inquiry form. The site uses HTML, CSS, JavaScript, and PHP.

## Pages and files

- `index.html` sends visitors to `frontpage.html`.
- `frontpage.html` is the home page and artist introduction.
- `gallery.html` contains the searchable, filterable gallery.
- `contact.php` contains the service inquiry form.
- `booking-process.php` handles form submissions.
- `frontpage.css` and `servicepage.css` provide the styles.
- `servicepagescript.js` contains shared menu and scroll behavior.
- `galleryscript.js` is currently empty; gallery behavior is written inline in `gallery.html`.

Keep the image and branding folders with the site when deploying; the pages refer to those assets using relative paths.

## Deploy to Amazon EC2

These instructions use an Ubuntu Server 24.04 LTS EC2 instance, Apache, PHP, and Certbot for a free Let's Encrypt TLS certificate. You will need an AWS account, a registered domain, and Windows OpenSSH (available on current Windows versions).

### 1. Launch and secure the EC2 instance

1. In the AWS EC2 console, launch an **Ubuntu Server 24.04 LTS** instance. Choose an instance size appropriate for your traffic and review AWS pricing.
2. Create and download a key pair (`.pem`). Keep it private and do not put it in the website folder.
3. Create or select a security group with these inbound rules:
   - **SSH**, TCP port `22`, source **My IP** (do not expose SSH to everyone).
   - **HTTP**, TCP port `80`, source `0.0.0.0/0` and `::/0`.
   - **HTTPS**, TCP port `443`, source `0.0.0.0/0` and `::/0`.
4. Allocate an **Elastic IP** in the EC2 console and associate it with this instance. Use this stable address for DNS rather than the instance's automatically assigned public IP. Check AWS pricing for Elastic IP charges.

### 2. Point your domain to the server

At your domain registrar or DNS provider, create these DNS records using the Elastic IP:

| Type | Name | Value |
|---|---|---|
| A | `@` (or the root domain) | Your Elastic IP |
| A | `www` | Your Elastic IP |

If the provider does not accept `@`, use the root domain itself. Remove conflicting A records. Do not add AAAA records unless IPv6 is configured for the instance. DNS changes can take time to propagate.

### 3. Install Apache and PHP

Connect from PowerShell, replacing the key path and IP with your own:

```powershell
ssh -i "$env:USERPROFILE\.ssh\YOUR_KEY.pem" ubuntu@ELASTIC_IP
```

Run these commands in the SSH session:

```bash
sudo apt update
sudo apt install -y apache2 php libapache2-mod-php unzip certbot python3-certbot-apache
sudo systemctl enable --now apache2
```

Apache serves public website files from `/var/www`. This setup uses `/var/www/panizmani` as the site's document root.

### 4. Upload the website

From PowerShell on your computer, run these commands in the project folder. The archive excludes this README, the mail test script, and Composer metadata; `test_mail.php` should not be published.

```powershell
Get-ChildItem -Force |
  Where-Object { $_.Name -notin @('README.md', 'test_mail.php', 'composer.json', 'panizmani-site.zip') } |
  Compress-Archive -DestinationPath .\panizmani-site.zip -Force

scp -i "$env:USERPROFILE\.ssh\YOUR_KEY.pem" .\panizmani-site.zip ubuntu@ELASTIC_IP:/tmp/panizmani-site.zip
```

Reconnect to the server if needed, then extract the upload and set ordinary read-only website permissions:

```bash
sudo mkdir -p /var/www/panizmani
sudo unzip -o /tmp/panizmani-site.zip -d /var/www/panizmani
sudo rm -f /var/www/panizmani/test_mail.php
sudo chown -R root:root /var/www/panizmani
sudo find /var/www/panizmani -type d -exec chmod 755 {} \;
sudo find /var/www/panizmani -type f -exec chmod 644 {} \;
```

### 5. Configure the Apache site

On the server, create `/etc/apache2/sites-available/panizmani.conf` with your actual domain in place of `example.com`:

```apache
<VirtualHost *:80>
    ServerName example.com
    ServerAlias www.example.com
    DocumentRoot /var/www/panizmani

    <Directory /var/www/panizmani>
        Require all granted
        AllowOverride None
        Options -Indexes
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/panizmani-error.log
    CustomLog ${APACHE_LOG_DIR}/panizmani-access.log combined
</VirtualHost>
```

You can create or edit the file with `sudo nano /etc/apache2/sites-available/panizmani.conf`, paste the configuration, then save and exit. Enable the site and check the Apache configuration:

```bash
sudo a2ensite panizmani.conf
sudo a2dissite 000-default.conf
sudo apache2ctl configtest
sudo systemctl reload apache2
```

`apache2ctl configtest` should report `Syntax OK`. Once DNS points to the server, visit `http://example.com` and `http://www.example.com` to confirm the site loads.

### 6. Enable HTTPS with Let's Encrypt

After both domain names resolve to the Elastic IP and HTTP works, run:

```bash
sudo certbot --apache -d example.com -d www.example.com
```

Follow the prompts and choose the option to redirect HTTP traffic to HTTPS. Verify the site at `https://example.com`, then verify that automatic certificate renewal works:

```bash
sudo certbot renew --dry-run
```

Keep ports 80 and 443 open; Certbot uses HTTP validation and Apache serves the site over HTTPS.

## Contact form email

The form posts to `booking-process.php`, which attempts to send the inquiry using PHP's `mail()` function. Installing Apache and PHP does not configure outbound email, so the form may not deliver messages until the server is connected to a properly configured mail service. The handler also does not send a separate confirmation email to the person submitting the form.
