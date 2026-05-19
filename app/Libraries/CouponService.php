<?php

namespace App\Libraries;

use App\Models\CouponModel;

/**
 * Centralised coupon validation + discount math.
 *
 * The active coupon code is stashed in the session under 'coupon_code'.
 * Discount is recomputed on every read so it always reflects the latest cart.
 */
class CouponService
{
    private const SESSION_KEY = 'coupon_code';

    public function applied(): ?string
    {
        $code = session(self::SESSION_KEY);
        return is_string($code) && $code !== '' ? $code : null;
    }

    public function clear(): void
    {
        session()->remove(self::SESSION_KEY);
    }

    /**
     * Validate a code against current cart subtotal. Does NOT apply.
     *
     * @return array{valid: bool, message: string, coupon?: array, discount?: float}
     */
    public function validate(string $code, float $subtotal): array
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return ['valid' => false, 'message' => 'Enter a coupon code.'];
        }

        $coupon = (new CouponModel())->where('code', $code)->first();
        if (! $coupon) {
            return ['valid' => false, 'message' => 'Coupon code is not recognised.'];
        }

        if (! empty($coupon['expires_at']) && strtotime($coupon['expires_at']) < time()) {
            return ['valid' => false, 'message' => 'This coupon has expired.'];
        }

        if ($coupon['max_uses'] !== null && (int) $coupon['used'] >= (int) $coupon['max_uses']) {
            return ['valid' => false, 'message' => 'This coupon has reached its use limit.'];
        }

        if ($subtotal < (float) $coupon['min_total']) {
            $missing = number_format((float) $coupon['min_total'] - $subtotal, 0, ',', '.');
            return ['valid' => false, 'message' => "Add Rp {$missing} more to your bag to use this coupon."];
        }

        return [
            'valid'    => true,
            'message'  => 'Coupon applied.',
            'coupon'   => $coupon,
            'discount' => $this->discountFor($coupon, $subtotal),
        ];
    }

    /**
     * Apply a code to the session if it validates.
     *
     * @return array{valid: bool, message: string, discount?: float}
     */
    public function apply(string $code, float $subtotal): array
    {
        $result = $this->validate($code, $subtotal);
        if ($result['valid']) {
            session()->set(self::SESSION_KEY, strtoupper(trim($code)));
        }
        return $result;
    }

    /**
     * Compute the discount for the currently-applied code against the
     * provided subtotal. Returns 0 if no code or invalid in current cart.
     */
    public function currentDiscount(float $subtotal): float
    {
        $code = $this->applied();
        if ($code === null) return 0.0;

        $result = $this->validate($code, $subtotal);
        if (! $result['valid']) {
            // Auto-clear silently — the cart no longer qualifies
            $this->clear();
            return 0.0;
        }
        return (float) $result['discount'];
    }

    /**
     * Increment used counter on a coupon. Called once per checkout.
     */
    public function recordUsage(string $code): void
    {
        $code   = strtoupper(trim($code));
        $model  = new CouponModel();
        $coupon = $model->where('code', $code)->first();
        if (! $coupon) return;
        $model->update($coupon['id'], ['used' => (int) $coupon['used'] + 1]);
    }

    private function discountFor(array $coupon, float $subtotal): float
    {
        $value = (float) $coupon['value'];
        if ($coupon['type'] === 'percent') {
            $discount = $subtotal * ($value / 100);
        } else {
            $discount = $value;
        }
        // Never let the discount exceed the subtotal
        return min(max($discount, 0), $subtotal);
    }
}
