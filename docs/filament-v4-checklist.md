## ✅ الطريقة الموصى بها (احترافية)

### 1️⃣ إنشاء ملف توثيق

من داخل جذر المشروع نفّذ:

```bash
mkdir docs
```

ثم:

```bash
code docs/filament-v4-checklist.md
```

(أو افتحه بأي محرر)

---

### 2️⃣ انسخ المحتوى ده داخل الملف

````md
# Filament v4 – Common Pitfalls Checklist

## 1) تأكد من الإصدار
```bash
composer show filament/filament
````

لو v4.x → استخدم تعليمات v4 فقط.

---

## 2) Actions في Filament v4

✅ الصحيح:

```php
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteBulkAction;
```

❌ الغلط (v3):

```php
use Filament\Tables\Actions\CreateAction;
```

---

## 3) Schema بدل Forms\Form

✅

```php
use Filament\Schemas\Schema;

public function form(Schema $schema): Schema
```

❌

```php
use Filament\Forms\Form;
```

---

## 4) تجنب alias اللي يبوّظ Intelephense

❌

```php
use Filament\Tables;
```

✅

```php
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
```

---

## 5) لو ظهر Class not found

تحقق من وجود الملف في vendor:

```bash
ls vendor/filament
```

لو مش موجود → غالبًا Namespace تغيّر.

---

## 6) روتين تنظيف سريع

```bash
composer dump-autoload
php artisan optimize:clear
```

ثم:

* Intelephense: Clear Cache
* Reload Window

---

## 7) لا تستبعد vendor من Intelephense

مسموح استبعاده من البحث فقط،
❌ ممنوع من `intelephense.files.exclude`.

````

---

### 3️⃣ (اختياري لكن مهم) أضفها للـ Git
```bash
git add docs/filament-v4-checklist.md
git commit -m "docs: add Filament v4 common pitfalls checklist"
````

---

## 🎯 النتيجة

* أي حد في المشروع يعرف **ليه الأحمر بيظهر**
* ما حدّش يخلط v3 مع v4 تاني
* توثيق رسمي يقلل الأعطال مستقبلًا

---

## 🚀 نكمل المشروع

الخطوة الجاية مباشرة:
👉 **فلترة BOQs حسب branch/company على مستوى Model + RelationManager**

ابعتلي:

* `Boq.php`
  أو
* قولّي تحب نبدأ بالفلترة ولا بتثبيت RelationManager داخل `ProjectResource`

ونكمل على طول.
