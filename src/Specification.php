<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

/**
 * The specification identifier of an invoice (BT-24) broken down into standard, profile and version.
 *
 * EN 16931 identifiers chain the core standard with the specifications built on it:
 * "#compliant#" marks a restriction (CIUS - still a valid EN 16931 invoice), "#conformant#" an
 * extension that adds information beyond EN 16931.
 */
final readonly class Specification
{
    /** Specification identifier of EN 16931 itself - the start of every identifier built on it. */
    public const EN16931 = 'urn:cen.eu:en16931:2017';

    private const FACTUR_X_PROFILES = [
        'minimum' => 'MINIMUM',
        'basicwl' => 'BASIC WL',
        'basic' => 'BASIC',
        'extended' => 'EXTENDED',
    ];

    /**
     * @param string $identifier the specification identifier as written in the invoice
     * @param string|null $profile MINIMUM, BASIC WL, BASIC, EXTENDED, EXTENDED-CTC-FR (ZUGFeRD / Factur-X),
     *                             CVD (XRechnung), Billing or Self-Billing (Peppol)
     * @param string|null $version version of the specification, e.g. 3.0 for XRechnung 3.0
     * @param bool $extension the invoice uses an extension beyond EN 16931 ("#conformant#")
     */
    public function __construct(
        public string $identifier,
        public Standard $standard,
        public ?string $profile = null,
        public ?string $version = null,
        public bool $extension = false,
    ) {}

    public static function parse(?string $identifier): self
    {
        $id = trim((string) $identifier);
        $extension = str_contains($id, '#conformant#');

        if ($id === '') {
            return new self('', Standard::Unknown);
        }

        if (preg_match('/urn\.cpro\.gouv\.fr:1p0:extended-ctc-fr/i', $id)) {
            return new self($id, Standard::FacturX, 'EXTENDED-CTC-FR', null, $extension);
        }

        if (preg_match('/urn:(factur-x\.eu:1p0|zugferd\.de:2p0):(minimum|basicwl|basic|extended)\b/i', $id, $m)) {
            $version = strtolower($m[1]) === 'zugferd.de:2p0' ? '2.0' : null;

            return new self($id, Standard::FacturX, self::FACTUR_X_PROFILES[strtolower($m[2])], $version, $extension);
        }

        if (preg_match('/xrechnung_(\d+(?:\.\d+)*)/i', $id, $m)) {
            $profile = preg_match('/xrechnung:cvd_/i', $id) ? 'CVD' : null;

            return new self($id, Standard::XRechnung, $profile, $m[1], $extension);
        }

        if (preg_match('/urn:fdc:peppol\.eu:2017:poacc:(billing|selfbilling)(?::international:([a-z]+))?:(\d+\.\d+)/i', $id, $m)) {
            $profile = strtolower($m[1]) === 'selfbilling' ? 'Self-Billing' : 'Billing';
            if ($m[2] !== '') {
                $profile .= ' (' . strtoupper($m[2]) . ')';
            }

            return new self($id, Standard::Peppol, $profile, $m[3], $extension);
        }

        if ($id === self::EN16931) {
            return new self($id, Standard::En16931);
        }

        return new self($id, str_starts_with($id, self::EN16931) ? Standard::Other : Standard::Unknown, null, null, $extension);
    }

    /**
     * ZUGFeRD / Factur-X MINIMUM and BASIC WL carry no invoice lines and do not satisfy EN 16931 -
     * they are a booking aid, not a complete invoice (in Germany not an electronic invoice).
     */
    public function isBookingAid(): bool
    {
        return $this->standard === Standard::FacturX && in_array($this->profile, ['MINIMUM', 'BASIC WL'], true);
    }

    /**
     * Readable name, e.g. "XRechnung 3.0", "ZUGFeRD / Factur-X EXTENDED", "Peppol BIS Billing 3.0".
     */
    public function name(): string
    {
        return match ($this->standard) {
            Standard::En16931 => 'EN 16931',
            Standard::XRechnung => trim('XRechnung ' . $this->version . ($this->profile !== null ? ' ' . $this->profile : '')
                . ($this->extension ? ' Extension' : '')),
            Standard::FacturX => match (true) {
                $this->profile === 'EXTENDED-CTC-FR' => 'Factur-X EXTENDED-CTC-FR',
                $this->version !== null => "ZUGFeRD {$this->version} {$this->profile}",
                default => "ZUGFeRD / Factur-X {$this->profile}",
            },
            Standard::Peppol => trim('Peppol BIS ' . $this->profile . ' ' . $this->version),
            Standard::Other => 'EN 16931 (' . $this->lastComponent() . ')',
            Standard::Unknown => $this->identifier,
        };
    }

    /**
     * The innermost specification of the chain, e.g. "urn:fdc:nen.nl:nlcius:v1.0".
     */
    private function lastComponent(): string
    {
        $parts = preg_split('/#(?:compliant|conformant)#/', $this->identifier) ?: [$this->identifier];

        return (string) end($parts);
    }
}
