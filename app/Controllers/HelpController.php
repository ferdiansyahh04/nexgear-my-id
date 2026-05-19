<?php

namespace App\Controllers;

class HelpController extends BaseController
{
    public function index()
    {
        return view('help/index', [
            'title'      => 'Help & FAQ',
            'categories' => $this->categories(),
        ]);
    }

    /**
     * FAQ catalogue. Edit here to add/remove questions — the page reads
     * directly from this static data structure (no DB).
     *
     * @return array<int, array{key: string, title: string, items: array<int, array{q: string, a: string}>}>
     */
    private function categories(): array
    {
        return [
            [
                'key'   => 'orders',
                'title' => 'Orders & Shipping',
                'items' => [
                    [
                        'q' => 'How long does delivery take?',
                        'a' => 'Standard orders ship within 1–2 business days. Domestic delivery typically arrives in 3–5 days; remote areas may take longer.',
                    ],
                    [
                        'q' => 'Can I track my order?',
                        'a' => 'Yes. Open <a href="/account/orders">Order History</a> in your account; the timeline shows the live status (Placed → Paid → Processing → Shipped → Delivered).',
                    ],
                    [
                        'q' => 'Do you ship internationally?',
                        'a' => 'Currently we ship within Indonesia only. International shipping is on the roadmap.',
                    ],
                    [
                        'q' => 'Is shipping free?',
                        'a' => 'Yes, every order ships at no extra cost. We absorb the shipping fee.',
                    ],
                ],
            ],
            [
                'key'   => 'returns',
                'title' => 'Returns & Refunds',
                'items' => [
                    [
                        'q' => 'What is your return policy?',
                        'a' => 'Unopened, unused items can be returned within 14 days of delivery. Email <a href="mailto:hello@nexgear.my.id">hello@nexgear.my.id</a> with your order number to start the process.',
                    ],
                    [
                        'q' => 'Can I cancel after I ordered?',
                        'a' => 'You can cancel while the order is still in <strong>Placed</strong> or <strong>Paid</strong> status. Once it moves to <strong>Processing</strong> or <strong>Shipped</strong>, contact support for assistance.',
                    ],
                    [
                        'q' => 'How long do refunds take?',
                        'a' => 'Refunds are issued back to your original payment method within 5 business days of confirmation.',
                    ],
                ],
            ],
            [
                'key'   => 'account',
                'title' => 'Account & Security',
                'items' => [
                    [
                        'q' => 'How do I change my password?',
                        'a' => 'A self-service password reset flow is on the roadmap. In the meantime, contact support and we will reset it for you.',
                    ],
                    [
                        'q' => 'Can I enable two-factor authentication?',
                        'a' => 'Yes — staff and admin accounts can enable TOTP-based 2FA via the admin Security panel. Customer-facing 2FA is coming soon.',
                    ],
                    [
                        'q' => 'Is my data safe?',
                        'a' => 'Passwords are stored using bcrypt, payment data is never logged, and all forms use CSRF tokens. We use rate limiting on auth endpoints to deter brute-force attempts.',
                    ],
                ],
            ],
            [
                'key'   => 'cart',
                'title' => 'Cart, Coupons & Wishlist',
                'items' => [
                    [
                        'q' => 'How do coupons work?',
                        'a' => 'Apply a coupon at checkout — the discount is calculated against your subtotal. Some codes have minimum cart values or use limits; the system tells you when one cannot be applied.',
                    ],
                    [
                        'q' => 'Can I save items for later?',
                        'a' => 'Yes — tap the heart icon on any product card to add it to your wishlist. Guests get a session-based wishlist that auto-merges into your account when you sign in.',
                    ],
                    [
                        'q' => 'Why did my coupon disappear?',
                        'a' => 'If the cart subtotal drops below the coupon\'s minimum, it is removed automatically. Re-apply once the cart qualifies again.',
                    ],
                ],
            ],
            [
                'key'   => 'products',
                'title' => 'Products',
                'items' => [
                    [
                        'q' => 'How do I know if something is in stock?',
                        'a' => 'The product page shows a live stock counter that polls every 30 seconds. Sold-out items show a "Notify Me" form — sign up and we will email you when stock returns.',
                    ],
                    [
                        'q' => 'Can I leave a review?',
                        'a' => 'Reviews are limited to verified buyers. Once your order has shipped, the review form unlocks on the product page.',
                    ],
                    [
                        'q' => 'How do I compare products?',
                        'a' => 'Tap the chart icon on any product card to add it to the compare tray. You can compare up to three products side by side.',
                    ],
                ],
            ],
        ];
    }
}
