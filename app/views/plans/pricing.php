<?php require APP_PATH . '/views/partials/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="material-symbols-rounded" style="font-size:48px;color:var(--md-primary)">diamond</span>
            <h1 class="fw-bold mt-2">Simple, Transparent Pricing</h1>
            <p class="text-muted lead">Choose the plan that fits your credentialing needs</p>
        </div>

        <!-- Billing Toggle -->
        <div class="text-center mb-4">
            <div class="btn-group" role="group" id="billingToggle">
                <button type="button" class="btn btn-outline-primary active" data-cycle="monthly">Monthly</button>
                <button type="button" class="btn btn-outline-primary" data-cycle="yearly">Yearly <span class="badge bg-success ms-1">Save 17%</span></button>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($plans as $plan):
                $features = json_decode($plan['features'] ?? '{}', true) ?: [];
                $isCurrent = $currentSub && (int)$currentSub['plan_id'] === (int)$plan['id'];
                $isPopular = $plan['slug'] === 'pro';
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm <?= $isPopular ? 'border-primary border-2' : '' ?>" style="border-radius:16px;overflow:hidden">
                    <?php if ($isPopular): ?>
                    <div class="bg-primary text-white text-center py-2 fw-semibold small">
                        <span class="material-symbols-rounded" style="font-size:16px;vertical-align:-3px">star</span>
                        Most Popular
                    </div>
                    <?php endif; ?>

                    <div class="card-body p-4">
                        <h3 class="fw-bold"><?= e($plan['name']) ?></h3>
                        <p class="text-muted small text-capitalize"><?= e($plan['type']) ?> Plan</p>

                        <div class="my-3">
                            <span class="display-5 fw-bold monthly-price">$<?= number_format((float)$plan['price_monthly'], 2) ?></span>
                            <span class="display-5 fw-bold yearly-price d-none">$<?= number_format((float)$plan['price_yearly'] / 12, 2) ?></span>
                            <span class="text-muted">/month</span>
                        </div>

                        <?php if ((float)$plan['price_yearly'] > 0): ?>
                        <p class="text-muted small yearly-total d-none">
                            $<?= number_format((float)$plan['price_yearly'], 2) ?> billed yearly
                        </p>
                        <?php endif; ?>

                        <hr>

                        <ul class="list-unstyled mb-4">
                            <li class="mb-2 d-flex align-items-start gap-2">
                                <span class="material-symbols-rounded text-success" style="font-size:18px">check_circle</span>
                                <span><strong><?= (int)$plan['max_credentials'] > 9999 ? 'Unlimited' : number_format((int)$plan['max_credentials']) ?></strong> credentials</span>
                            </li>
                            <li class="mb-2 d-flex align-items-start gap-2">
                                <span class="material-symbols-rounded text-success" style="font-size:18px">check_circle</span>
                                <span><strong><?= (int)$plan['max_users'] ?></strong> team member<?= (int)$plan['max_users'] > 1 ? 's' : '' ?></span>
                            </li>
                            <?php
                            $featureLabels = [
                                'pdf_download' => 'PDF Download',
                                'basic_badge' => 'Basic Badges',
                                'custom_badge' => 'Custom Badge Builder',
                                'evidence' => 'Evidence Linking',
                                'bulk_upload' => 'Bulk CSV Issuance',
                                'priority_support' => 'Priority Support',
                                'api_access' => 'API Access',
                                'team_management' => 'Team Management',
                                'white_label' => 'White Label',
                                'custom_domain' => 'Custom Domain',
                                'sso' => 'SSO Integration',
                            ];
                            foreach ($featureLabels as $key => $label):
                                $has = !empty($features[$key]);
                            ?>
                            <li class="mb-2 d-flex align-items-start gap-2">
                                <?php if ($has): ?>
                                <span class="material-symbols-rounded text-success" style="font-size:18px">check_circle</span>
                                <?php else: ?>
                                <span class="material-symbols-rounded text-muted" style="font-size:18px">cancel</span>
                                <?php endif; ?>
                                <span class="<?= $has ? '' : 'text-muted' ?>"><?= $label ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <?php if ($isCurrent): ?>
                            <button class="btn btn-outline-success w-100 rounded-pill" disabled>
                                <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">check</span>
                                Current Plan
                            </button>
                        <?php elseif (isLoggedIn()): ?>
                            <form method="POST" action="<?= url('plan/subscribe') ?>">
                                <?= csrfField() ?>
                                <input type="hidden" name="plan" value="<?= e($plan['slug']) ?>">
                                <input type="hidden" name="billing_cycle" value="monthly" class="billing-cycle-input">
                                <?php if ((float)$plan['price_monthly'] === 0.0): ?>
                                    <button type="submit" class="btn btn-outline-primary w-100 rounded-pill">
                                        Get Started Free
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-primary w-100 rounded-pill">
                                        <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">shopping_cart</span>
                                        Subscribe Now
                                    </button>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <a href="<?= url('register') ?>" class="btn btn-primary w-100 rounded-pill">
                                Get Started
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($currentSub && $currentSub['plan_slug'] !== 'free'): ?>
        <div class="text-center mt-5">
            <form method="POST" action="<?= url('plan/cancel') ?>" onsubmit="return confirm('Are you sure you want to cancel?')">
                <?= csrfField() ?>
                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                    Cancel Subscription
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- FAQ -->
        <div class="mt-5 pt-5">
            <h3 class="text-center fw-bold mb-4">Frequently Asked Questions</h3>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0 mb-2">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Can I upgrade my plan later?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">Yes! You can upgrade at any time. Your new plan takes effect immediately.</div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-2">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">We'll be integrating Stripe for secure payments. Currently plans are activated as trial.</div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-2">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Are credentials W3C compliant?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">Yes! All issued credentials follow Open Badges 3.0 and W3C Verifiable Credentials standards with Ed25519 digital signatures.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('billingToggle');
    if (!toggle) return;
    toggle.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', function() {
            toggle.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const cycle = this.dataset.cycle;
            document.querySelectorAll('.monthly-price').forEach(el => el.classList.toggle('d-none', cycle === 'yearly'));
            document.querySelectorAll('.yearly-price').forEach(el => el.classList.toggle('d-none', cycle === 'monthly'));
            document.querySelectorAll('.yearly-total').forEach(el => el.classList.toggle('d-none', cycle === 'monthly'));
            document.querySelectorAll('.billing-cycle-input').forEach(el => el.value = cycle);
        });
    });
});
</script>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
