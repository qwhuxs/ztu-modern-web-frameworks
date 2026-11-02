<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261102101958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointments (id INT AUTO_INCREMENT NOT NULL, appointment_at DATETIME NOT NULL, complaints LONGTEXT DEFAULT NULL, patient_id INT NOT NULL, doctor_id INT NOT NULL, INDEX IDX_6A41727A6B899279 (patient_id), INDEX IDX_6A41727A87F4FB17 (doctor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE diagnoses (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, appointment_id INT NOT NULL, INDEX IDX_D2644031E5B533F9 (appointment_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE doctors (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, specialty VARCHAR(100) NOT NULL, phone VARCHAR(20) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE patients (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, birth_date DATE NOT NULL, phone VARCHAR(20) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE treatments (id INT AUTO_INCREMENT NOT NULL, prescription LONGTEXT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, diagnosis_id INT NOT NULL, INDEX IDX_4A48CE0D3CBE4D00 (diagnosis_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A6B899279 FOREIGN KEY (patient_id) REFERENCES patients (id)');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A87F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctors (id)');
        $this->addSql('ALTER TABLE diagnoses ADD CONSTRAINT FK_D2644031E5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointments (id)');
        $this->addSql('ALTER TABLE treatments ADD CONSTRAINT FK_4A48CE0D3CBE4D00 FOREIGN KEY (diagnosis_id) REFERENCES diagnoses (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A6B899279');
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A87F4FB17');
        $this->addSql('ALTER TABLE diagnoses DROP FOREIGN KEY FK_D2644031E5B533F9');
        $this->addSql('ALTER TABLE treatments DROP FOREIGN KEY FK_4A48CE0D3CBE4D00');
        $this->addSql('DROP TABLE appointments');
        $this->addSql('DROP TABLE diagnoses');
        $this->addSql('DROP TABLE doctors');
        $this->addSql('DROP TABLE patients');
        $this->addSql('DROP TABLE treatments');
    }
}
