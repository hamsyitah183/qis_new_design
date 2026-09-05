try {
    view('email.setup_password_bm', ['setupUrl' => 'test'])->render();
    view('email.verify_bm', ['url' => 'test'])->render();
    view('email.reset_password_bm', ['resetUrl' => 'test'])->render();
    view('email.qis_news_bm', ['title' => 'test', 'news' => 'test'])->render();
    view('email.application_email_bm', ['title' => 'test', 'news' => 'test'])->render();
    echo "ALL_GOOD";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
