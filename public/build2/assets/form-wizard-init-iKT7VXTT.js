(function () {
    // 🟢 First wizard
    let firstWizardConfig = {
        wz_class: ".wizard-tab",
        highlight: true,
        highlight_time: 1000,
        progress: true,
        validate: true
    };
    new Wizard1(firstWizardConfig).init();

    // 🟢 Second wizard (with progress bar)
    let secondWizardConfig = {
        wz_class: ".wizard-second-tab",   // ✅ fixed selector
        highlight: true,
        highlight_time: 1000,
        progress: true,
        validate: true
    };
    new Wizard1(secondWizardConfig).init();

    // Other wizard initializations
    // flatpickr("#date", {});
    // new Wizard("#basicwizard", { validate: true });
    // new Wizard("#progresswizard", { validate: true, progress: true });

    
})();
