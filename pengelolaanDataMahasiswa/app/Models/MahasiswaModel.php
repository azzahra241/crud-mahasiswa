<?php

declare(strict_types=1);

class MahasiswaModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(): array
    {
        try {
            return $this->pdo->query(
                'SELECT id, nim, nama, email, prodi, angkatan, ipk
                 FROM mahasiswa
                 WHERE deleted_at IS NULL
                 ORDER BY nama ASC, id ASC'
            )->fetchAll();
        } catch (PDOException $e) {
            $this->logError('all', $e);
            throw $e;
        }
    }

    public function find(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT id, nim, nama, email, prodi, angkatan, ipk
                 FROM mahasiswa
                 WHERE id = :id AND deleted_at IS NULL'
            );
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();

            return $row === false ? null : $row;
        } catch (PDOException $e) {
            $this->logError('find', $e);
            throw $e;
        }
    }

    public function existsByNim(string $nim, ?int $excludeId = null): bool
    {
        try {
            $sql = 'SELECT COUNT(*) FROM mahasiswa WHERE nim = :nim';
            $params = ['nim' => $nim];

            if ($excludeId !== null) {
                $sql .= ' AND id <> :exclude_id';
                $params['exclude_id'] = $excludeId;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->logError('existsByNim', $e);
            throw $e;
        }
    }

    public function create(
        string $nim,
        string $nama,
        ?string $email = null,
        ?string $prodi = null,
        ?int $angkatan = null
    ): void {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO mahasiswa (nim, nama, email, prodi, angkatan)
                 VALUES (:nim, :nama, :email, :prodi, :angkatan)'
            );
            $stmt->execute([
                'nim'      => $nim,
                'nama'     => $nama,
                'email'    => $email,
                'prodi'    => $prodi,
                'angkatan' => $angkatan,
            ]);
        } catch (PDOException $e) {
            $this->logError('create', $e);
            throw $e;
        }
    }

    public function update(
        int $id,
        string $nim,
        string $nama,
        ?string $email = null,
        ?string $prodi = null,
        ?int $angkatan = null
    ): void {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE mahasiswa
                 SET nim = :nim,
                     nama = :nama,
                     email = COALESCE(:email, email),
                     prodi = COALESCE(:prodi, prodi),
                     angkatan = COALESCE(:angkatan, angkatan)
                 WHERE id = :id AND deleted_at IS NULL'
            );
            $stmt->execute([
                'nim'      => $nim,
                'nama'     => $nama,
                'email'    => $email,
                'prodi'    => $prodi,
                'angkatan' => $angkatan,
                'id'       => $id,
            ]);
        } catch (PDOException $e) {
            $this->logError('update', $e);
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE mahasiswa SET deleted_at = NOW()
                 WHERE id = :id AND deleted_at IS NULL'
            );
            $stmt->execute(['id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->logError('delete', $e);
            throw $e;
        }
    }

    public function hasKrs(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*)
                 FROM krs k
                 JOIN mahasiswa m
                   ON k.nim COLLATE utf8mb4_unicode_ci = m.nim COLLATE utf8mb4_unicode_ci
                 WHERE m.id = :id'
            );
            $stmt->execute(['id' => $id]);

            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->logError('hasKrs', $e);
            throw $e;
        }
    }

    private function logError(string $method, PDOException $e): void
    {
        error_log(sprintf('[MahasiswaModel::%s] %s', $method, $e->getMessage()));
    }
}