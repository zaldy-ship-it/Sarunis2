<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * @return array<string, array{
     *   label:string,
     *   dashboard:string,
     *   requires_all:bool,
     *   roles:array<int, string>
     * }>
     */
    public static function portalMap(): array
    {
        return [
            'admin' => [
                'label' => 'Admin Sekolah',
                'dashboard' => '/admin/dashboard',
                'requires_all' => false,
                'roles' => [UserRole::ADMIN->value],
            ],
            'guru-mapel' => [
                'label' => 'Guru Mapel',
                'dashboard' => '/guru-mapel/dashboard',
                'requires_all' => false,
                'roles' => [UserRole::GURU_MAPEL->value],
            ],
            'walikelas' => [
                'label' => 'Wali Kelas',
                'dashboard' => '/walikelas/dashboard',
                'requires_all' => false,
                'roles' => [UserRole::GURU_MAPEL->value],
            ],
            'orang-tua' => [
                'label' => 'Orang Tua',
                'dashboard' => '/orang-tua/dashboard',
                'requires_all' => false,
                'roles' => [UserRole::ORANG_TUA->value],
            ],
            'wakasek-kesiswaan' => [
                'label' => 'Wakasek Kesiswaan',
                'dashboard' => '/wakasek-kesiswaan/dashboard',
                'requires_all' => false,
                'roles' => [UserRole::WAKASEK_KESISWAAN->value],
            ],
            'guru-piket' => [
                'label' => 'Guru Piket',
                'dashboard' => '/guru-piket/dashboard',
                'requires_all' => false,
                'roles' => [UserRole::GURU_PIKET->value],
            ],
            'siswa' => [
                'label' => 'Siswa',
                'dashboard' => '/siswa/dashboard',
                'requires_all' => false,
                'roles' => [UserRole::SISWA->value],
            ],
        ];
    }

    /**
     * @return array<string, array{
     *   display:string,
     *   asset:string
     * }>
     */
    public static function portalViewMap(): array
    {
        return [
            'admin' => [
                'display' => 'Admin',
                'asset' => 'admin',
            ],
            'guru-mapel' => [
                'display' => 'Guru Mapel',
                'asset' => 'school',
            ],
            'walikelas' => [
                'display' => 'Wali Kelas',
                'asset' => 'school',
            ],
            'orang-tua' => [
                'display' => 'Orang Tua',
                'asset' => 'school',
            ],
            'wakasek-kesiswaan' => [
                'display' => 'Wakasek Kesiswaan',
                'asset' => 'school',
            ],
            'guru-piket' => [
                'display' => 'Guru Piket',
                'asset' => 'school',
            ],
            'siswa' => [
                'display' => 'Siswa',
                'asset' => 'school',
            ],
        ];
    }

    /**
     * @return array{display:string,asset:string}|null
     */
    public static function portalView(string $portal): ?array
    {
        return static::portalViewMap()[$portal] ?? null;
    }

    public function login(Request $request, array $credentials, bool $remember = false, ?string $portal = null): array
    {
        if (! Auth::guard('web')->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak valid.'],
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user()->load(['teacherProfile', 'studentProfile']);

        if ($portal === null && $this->defaultPortal($user) === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Akun ini belum memiliki hak akses ke portal mana pun.');
        }

        if ($portal !== null && ! $this->canAccessPortal($user, $portal)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Akun ini tidak memiliki akses ke portal yang dipilih.');
        }

        return $this->buildPayload($user, $portal);
    }

    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function buildPayload(User $user, ?string $activePortal = null): array
    {
        $availablePortals = $this->availablePortals($user);
        $selectedPortal = $activePortal ?? $this->defaultPortal($user);
        $portalLabel = $selectedPortal !== null
            ? (static::portalMap()[$selectedPortal]['label'] ?? null)
            : null;

        return [
            'user' => $user,
            'logged_in_user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'logged_in_as' => $portalLabel,
            'roles' => $user->roles ?? [],
            'available_portals' => $availablePortals,
            'active_portal' => $selectedPortal,
            'redirect_to' => $selectedPortal !== null
                ? static::portalMap()[$selectedPortal]['dashboard']
                : null,
        ];
    }

    /**
     * @return array<int, array{key:string,label:string,dashboard:string}>
     */
    public function availablePortals(User $user): array
    {
        $portals = [];

        foreach (static::portalMap() as $key => $portal) {
            if ($this->canAccessPortal($user, $key)) {
                $portals[] = [
                    'key' => $key,
                    'label' => $portal['label'],
                    'dashboard' => $portal['dashboard'],
                ];
            }
        }

        return $portals;
    }

    public function canAccessPortal(User $user, string $portal): bool
    {
        $portalConfig = static::portalMap()[$portal] ?? null;

        if ($portalConfig === null) {
            return false;
        }

        if ($portal === 'admin') {
            return $user->hasRole(UserRole::ADMIN);
        }

        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        if ($portal === 'walikelas') {
            return $user->hasRole(UserRole::GURU_MAPEL) &&
                $user->teacherProfile !== null &&
                $user->teacherProfile->homeroomClasses()->exists();
        }

        if ($portal === 'orang-tua') {
            return $user->hasRole(UserRole::ORANG_TUA) &&
                $user->parentStudents()->exists();
        }

        if ($portal === 'guru-mapel') {
            return $user->hasRole(UserRole::GURU_MAPEL) &&
                $user->teacherProfile !== null &&
                $user->teacherProfile->hasSubjectRole();
        }

        return $portalConfig['requires_all']
            ? $user->hasAllRoles($portalConfig['roles'])
            : $user->hasAnyRole($portalConfig['roles']);
    }

    public function defaultPortal(User $user): ?string
    {
        foreach (['admin', 'wakasek-kesiswaan', 'guru-piket', 'orang-tua', 'guru-mapel', 'walikelas', 'siswa'] as $portal) {
            if ($this->canAccessPortal($user, $portal)) {
                return $portal;
            }
        }

        return null;
    }
}
