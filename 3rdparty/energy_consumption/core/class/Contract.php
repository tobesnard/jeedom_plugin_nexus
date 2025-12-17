<?php

namespace Nexus\Energy\Electricity;

require_once __DIR__ . "/../../vendor/autoload.php";

class Contract
{
    public \DateTimeImmutable $startDate;
    public ?\DateTimeImmutable $endDate;
    public float $kwhPrice;
    public float $subscriptionPrice;
    public string $tariffOption;
    public ?string $accountNumber;
    public ?string $deliveryPointId;
    public ?string $offerName;

    public function __construct(
        \DateTimeImmutable $startDate,
        float $kwhPrice,
        float $monthlySubscription,
        string $tariffOption,
        ?\DateTimeImmutable $endDate = null,
        ?string $accountNumber = null,
        ?string $deliveryPointId = null,
        ?string $offerName = null
    ) {
        $this->startDate = $startDate;
        $this->kwhPrice = $kwhPrice;
        $this->monthlySubscription = $monthlySubscription;
        $this->tariffOption = $tariffOption;
        $this->endDate = $endDate;
        $this->accountNumber = $accountNumber;
        $this->deliveryPointId = $deliveryPointId;
        $this->offerName = $offerName;
    }

    // --- Getters ---

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getKwhPrice(): float
    {
        return $this->kwhPrice;
    }

    public function getAnnualSubscriptionCost(): float
    {
        return $this->monthlySubscription * 12;
    }

    public function getMonthlySubscription(): float
    {
        return $this->monthlySubscription;
    }
    public function getTariffOption(): string
    {
        return $this->tariffOption;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function getDeliveryPointId(): ?string
    {
        return $this->deliveryPointId;
    }

    public function getOfferName(): ?string
    {
        return $this->offerName;
    }
}
