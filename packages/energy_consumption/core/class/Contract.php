<?php

namespace Nexus\Energy\Electricity;

/**
 * Représente un contrat d'électricité.
 *
 * Propriétés principales :
 * - `startDate` / `endDate` : période de validité du contrat
 * - `kwhPrice` : prix du kWh appliqué
 * - `monthlySubscription` : montant mensuel de l'abonnement
 * - autres métadonnées : `accountNumber`, `deliveryPointId`, `offerName`
 */
class Contract
{
    public \DateTimeImmutable $startDate;
    public ?\DateTimeImmutable $endDate;
    public float $kwhPrice;
    /**
     * Prix d'abonnement mensuel (en unités monétaires).
     * Nom unifié : `monthlySubscription` pour cohérence avec les getters.
     */
    public float $monthlySubscription;
    public string $tariffOption;
    public ?string $accountNumber;
    public ?string $deliveryPointId;
    public ?string $offerName;

    /**
     * @param \DateTimeImmutable $startDate Date de début du contrat
     * @param float $kwhPrice Prix du kWh
     * @param float $monthlySubscription Abonnement mensuel
     * @param string $tariffOption Option tarifaire (ex: mono/tempo)
     * @param \DateTimeImmutable|null $endDate Date de fin (si null => actif)
     * @param string|null $accountNumber Référence client
     * @param string|null $deliveryPointId Identifiant point de livraison (PDR)
     * @param string|null $offerName Nom de l'offre commerciale
     */
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
