<?php
declare(strict_types=1);

/**
 * Kelas induk: User.
 * Menyimpan data dasar pengguna dan perilaku umum akun.
 */
class User {
    protected int $id;
    protected string $name;
    protected string $email;
    protected string $passwordHash;
    protected string $createdAt;

    public function __construct(array $data) {
        $this->id=(int)$data['id'];
        $this->name=(string)$data['name'];
        $this->email=(string)$data['email'];
        $this->passwordHash=(string)$data['password_hash'];
        $this->createdAt=(string)$data['created_at'];
    }
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function verifyPassword(string $password): bool { return password_verify($password,$this->passwordHash); }
    public function updateName(string $name): void { $this->name=trim($name); }
    public function getProfile(): array {
        return ['id'=>$this->id,'name'=>$this->name,'email'=>$this->email,'created_at'=>$this->createdAt];
    }
}

/**
 * Kelas turunan: WalletUser mewarisi seluruh data dan method User.
 * Atribut saldo dan PIN dibuat private agar hanya dapat diubah melalui method.
 */
class WalletUser extends User {
    private int $walletId;
    private float $balance;
    private string $walletStatus;
    private string $pin;
    private string $updatedAt;

    public function __construct(array $data) {
        parent::__construct($data);
        $this->walletId=(int)$data['wallet_id'];
        $this->balance=(float)$data['balance'];
        $this->walletStatus=(string)$data['wallet_status'];
        $this->pin=(string)$data['pin'];
        $this->updatedAt=(string)$data['updated_at'];
    }
    public function getBalance(): float { return $this->balance; }
    public function topUp(float $amount): void {
        if ($amount<=0) throw new InvalidArgumentException('Nominal top up harus lebih dari nol.');
        $this->balance += $amount;
    }
    public function debit(float $amount): void {
        if ($amount<=0) throw new InvalidArgumentException('Nominal transaksi tidak valid.');
        if ($amount>$this->balance) throw new RuntimeException('Saldo KOINY tidak mencukupi.');
        $this->balance -= $amount;
    }
    public function credit(float $amount): void {
        if ($amount<=0) throw new InvalidArgumentException('Nominal transaksi tidak valid.');
        $this->balance += $amount;
    }
    public function getWalletSummary(): array {
        return ['wallet_id'=>$this->walletId,'balance'=>$this->balance,'status'=>$this->walletStatus,'updated_at'=>$this->updatedAt];
    }
}
