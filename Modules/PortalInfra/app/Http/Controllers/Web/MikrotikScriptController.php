<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\MikrotikServer;

class MikrotikScriptController extends Controller
{
    public function index(Request $request)
    {
        $servers = MikrotikServer::where('is_active', true)->orderBy('name')->get();
        return view('infra::mikrotik.scripts', compact('servers'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:mikrotik_servers,id',
            'script_type' => 'required|in:pppoe,hotspot,firewall,dhcp,complete',
            'wan_interface' => 'nullable|string|max:50',
            'lan_interface' => 'nullable|string|max:50',
            'wan_mode' => 'nullable|in:dhcp,static,pppoe',
            'wan_ip' => 'nullable|string|max:50',
            'wan_mask' => 'nullable|string|max:10',
            'wan_gateway' => 'nullable|string|max:50',
            'wan_pppoe_user' => 'nullable|string|max:50',
            'wan_pppoe_password' => 'nullable|string|max:50',
            'lan_ip' => 'nullable|string|max:50',
            'lan_mask' => 'nullable|string|max:10',
            'pool_name' => 'nullable|string|max:50',
            'pool_start' => 'nullable|string|max:50',
            'pool_end' => 'nullable|string|max:50',
            'pppoe_service_name' => 'nullable|string|max:50',
            'hotspot_name' => 'nullable|string|max:50',
            'dns_servers' => 'nullable|string|max:200',
            'ntp_server' => 'nullable|string|max:100',
            'admin_password' => 'nullable|string|max:50',
            'mtu_wan' => 'nullable|integer',
            'mtu_lan' => 'nullable|integer',
            'bandwidth_up' => 'nullable|string|max:20',
            'bandwidth_down' => 'nullable|string|max:20',
        ]);

        $server = MikrotikServer::findOrFail($validated['server_id']);
        $script = $this->buildScript($validated, $server);

        return view('infra::mikrotik.script-result', compact('script', 'server', 'validated'));
    }

    private function buildScript(array $config, MikrotikServer $server): string
    {
        $wan = $config['wan_interface'] ?? 'ether5';
        $lan = $config['lan_interface'] ?? 'ether1';
        $wanMode = $config['wan_mode'] ?? 'dhcp';
        $wanIp = $config['wan_ip'] ?? '';
        $wanMask = $config['wan_mask'] ?? '24';
        $wanGateway = $config['wan_gateway'] ?? '';
        $wanPppoeUser = $config['wan_pppoe_user'] ?? '';
        $wanPppoePass = $config['wan_pppoe_password'] ?? '';
        $lanIp = $config['lan_ip'] ?? '192.168.1.1';
        $lanMask = $config['lan_mask'] ?? '24';
        $lanSubnet = $this->subnetAddress($lanIp, $lanMask);
        $pppoe = $this->pppoeSubnet($lanIp);
        $poolName = $config['pool_name'] ?? 'pool-hotspot';
        $poolStart = $config['pool_start'] ?? '192.168.1.10';
        $poolEnd = $config['pool_end'] ?? '192.168.1.250';
        $pppoeService = $config['pppoe_service_name'] ?? 'pppoe-service';
        $hotspotName = $config['hotspot_name'] ?? 'hotspot1';
        $dns = $config['dns_servers'] ?? '8.8.8.8, 8.8.4.4';
        $ntp = $config['ntp_server'] ?? 'pool.ntp.org';
        $adminPass = $config['admin_password'] ?? '';
        $mtuWan = $config['mtu_wan'] ?? 1500;
        $mtuLan = $config['mtu_lan'] ?? 1500;
        $bandwidthUp = $config['bandwidth_up'] ?? '10M';
        $bandwidthDown = $config['bandwidth_down'] ?? '50M';
        $scriptType = $config['script_type'];

        $lines = [];
        $lines[] = '# ============================================';
        $lines[] = '# MyISP - Configuracao MikroTik RouterOS';
        $lines[] = '# Servidor: ' . $server->name . ' (' . $server->ip . ')';
        $lines[] = '# Gerado em: ' . now()->format('d/m/Y H:i:s');
        $lines[] = '# Tipo: ' . strtoupper($scriptType);
        $lines[] = '# ============================================';
        $lines[] = '';
        $lines[] = '# IMPORTANTE: Cole este script no Terminal do WinBox';
        $lines[] = '# ou via SSH. Execute linha por linha ou copie tudo.';
        $lines[] = '';
        $lines[] = '# --------------------------------------------';
        $lines[] = '# 1. IDENTIFICACAO DO SERVIDOR';
        $lines[] = '# --------------------------------------------';
        $lines[] = '/system identity set name="' . $server->name . '"';
        $lines[] = '';

        if ($adminPass) {
            $lines[] = '# --------------------------------------------';
            $lines[] = '# 2. SENHA DO ADMINISTRADOR';
            $lines[] = '# --------------------------------------------';
            $lines[] = '/user set admin password="' . $adminPass . '"';
            $lines[] = '';
        }

        $lines[] = '# --------------------------------------------';
        $lines[] = '# ' . ($adminPass ? '3' : '2') . '. CONFIGURACAO DE REDE';
        $lines[] = '# --------------------------------------------';
        $lines[] = '/ip address add address=' . $lanIp . '/' . $lanMask . ' interface=' . $lan . ' comment="LAN - MyISP"';
        $lines[] = '/ip dns set servers=' . $dns . ' allow-remote-requests=yes';
        $lines[] = '# --- Acesso WAN (' . strtoupper($wanMode) . ') ---';

        if ($wanMode === 'static') {
            $lines[] = '/ip address add address=' . $wanIp . '/' . $wanMask . ' interface=' . $wan . ' comment="WAN - IP Fixo"';
            $lines[] = '/ip route add dst-address=0.0.0.0/0 gateway=' . $wanGateway . ' comment="Rota Default - WAN"';
        } elseif ($wanMode === 'pppoe') {
            $lines[] = '/interface pppoe-client add name="wan-pppoe" interface=' . $wan . ' user="' . $wanPppoeUser . '" password="' . $wanPppoePass . '" disabled=no add-default-route=yes use-peer-dns=no comment="WAN - PPPoE"';
            $lines[] = '/ip route add dst-address=0.0.0.0/0 gateway="wan-pppoe" comment="Rota Default - WAN"';
        } else {
            $lines[] = '/ip dhcp-client add interface=' . $wan . ' disabled=no add-default-route=yes use-peer-dns=no comment="WAN - DHCP"';
        }
        $lines[] = '/system ntp client set enabled=yes';
        $lines[] = '/system ntp client servers add address=' . $ntp;
        $lines[] = '/ip pool add name="' . $poolName . '" ranges=' . $poolStart . '-' . $poolEnd;
        $lines[] = '';

        $lineNum = $adminPass ? 4 : 3;

        if ($scriptType === 'pppoe' || $scriptType === 'complete') {
            $lines[] = '# --------------------------------------------';
            $lines[] = '# ' . $lineNum . '. SERVIDOR PPPoE';
            $lines[] = '# --------------------------------------------';
            $lines[] = '/ip pool add name="pool-pppoe" ranges=' . $pppoe['start'] . '-' . $pppoe['end'];
            $lines[] = '/interface pppoe-server server add service-name="' . $pppoeService . '" interface=' . $lan . ' max-mtu=1492 max-mru=1492 authentication=pap,chap,mschap1,mschap2 default-profile=default one-session-per-host=yes disabled=no';
            $lines[] = '/ppp profile add name="pppoe-profile" local-address=' . $pppoe['gateway'] . ' remote-address="pool-pppoe" dns-server=' . $dns . ' use-upnp=no';
            $lines[] = '/ppp aaa set use-radius=yes accounting=yes interim-update=5m';
            $lines[] = '';
            $lineNum++;
        }

        if ($scriptType === 'hotspot' || $scriptType === 'complete') {
            $lines[] = '# --------------------------------------------';
            $lines[] = '# ' . $lineNum . '. SERVIDOR HOTSPOT';
            $lines[] = '# --------------------------------------------';
            $lines[] = '/ip hotspot profile add name="hsprof1" hotspot-address=' . $lanIp . ' dns-name="hotspot.' . $lanIp . '.sslip.io" html-directory=hotspot';
            $lines[] = '/ip hotspot add name="' . $hotspotName . '" interface=' . $lan . ' profile="hsprof1" address-pool="' . $poolName . '" addresses="' . $lanIp . '/' . $lanMask . '"';
            $lines[] = '/ip hotspot user profile add name="hs-user-profile" idle-time=5m session-timeout=0s rate-limit="' . $bandwidthDown . '/' . $bandwidthUp . '"';
            $lines[] = '/ip hotspot user add name="admin" password="admin" profile="hs-user-profile" server="' . $hotspotName . '" comment="Usuario administrativo"';
            $lines[] = '';
            $lineNum++;
        }

        if ($scriptType === 'firewall' || $scriptType === 'pppoe' || $scriptType === 'complete') {
            $lines[] = '# --------------------------------------------';
            $lines[] = '# ' . $lineNum . '. FIREWALL / NAT';
            $lines[] = '# --------------------------------------------';
            $lines[] = '# Regra NAT para saida da WAN (masquerade)';
            $lines[] = '/ip firewall nat add chain=srcnat out-interface=' . $wan . ' action=masquerade comment="NAT - Saida WAN"';
            $lines[] = '';
            $lines[] = '# Regras de protecao';
            $lines[] = '/ip firewall filter add chain=input connection-state=established,related action=accept comment="Permitir Conexoes Estabelecidas"';
            $lines[] = '/ip firewall filter add chain=input connection-state=invalid action=drop comment="Dropar Conexoes Invalidas"';
            $lines[] = '/ip firewall filter add chain=input protocol=icmp action=accept comment="Permitir ICMP"';
            $lines[] = '/ip firewall filter add chain=input dst-port=8291 protocol=tcp src-address=' . $lanSubnet . ' action=accept comment="Permitir WinBox LAN"';
            $lines[] = '/ip firewall filter add chain=input dst-port=8728 protocol=tcp src-address=' . $lanSubnet . ' action=accept comment="Permitir API RouterOS - MyISP (LAN)"';
            $lines[] = '/ip firewall filter add chain=input dst-port=8291 protocol=tcp action=drop comment="Bloquear WinBox WAN"';
            $lines[] = '/ip firewall filter add chain=input dst-port=8728 protocol=tcp action=drop comment="Bloquear API RouterOS WAN"';
            $lines[] = '/ip firewall filter add chain=input protocol=tcp dst-port=23 action=drop comment="Bloquear Telnet"';
            $lines[] = '/ip firewall filter add chain=input protocol=tcp dst-port=21 action=drop comment="Bloquear FTP"';
            $lines[] = '';
            $lines[] = '# Address List - Bloqueio de clientes inadimplentes (MyISP)';
            $lines[] = '/ip firewall address-list add list=myisp-blocked address=0.0.0.0 comment="Lista para bloqueio MyISP"';
            $lines[] = '/ip firewall filter add chain=forward src-address-list=myisp-blocked action=drop comment="Bloquear Inadimplentes - Upload"';
            $lines[] = '/ip firewall filter add chain=forward dst-address-list=myisp-blocked action=drop comment="Bloquear Inadimplentes - Download"';
            $lines[] = '';

            if ($scriptType === 'pppoe' || $scriptType === 'complete') {
                $lines[] = '# Internet somente para clientes PPPoE autenticados';
                $lines[] = '/ip firewall filter add chain=forward connection-state=established,related action=accept comment="Permitir Conexoes Estabelecidas - Forward"';
                $lines[] = '/ip firewall filter add chain=forward connection-state=invalid action=drop comment="Dropar Conexoes Invalidas - Forward"';
                $lines[] = '/ip firewall filter add chain=forward connection-state=new src-address=' . $pppoe['subnet'] . ' out-interface=' . $wan . ' action=accept comment="Internet - Cliente PPPoE autenticado"';
                $lines[] = '/ip firewall filter add chain=forward connection-state=new out-interface=' . $wan . ' action=drop comment="Bloquear internet sem PPPoE"';
                $lines[] = '';
            }

            $lineNum++;
        }

        if ($scriptType === 'dhcp' || $scriptType === 'complete') {
            $lines[] = '# --------------------------------------------';
            $lines[] = '# ' . $lineNum . '. SERVIDOR DHCP';
            $lines[] = '# --------------------------------------------';
            $lines[] = '/ip pool add name="dhcp-pool" ranges=' . $poolStart . '-' . $poolEnd;
            $lines[] = '/ip dhcp-server network add address=' . $lanIp . '/' . $lanMask . ' dns-server=' . $dns . ' gateway=' . $lanIp;
            $lines[] = '/ip dhcp-server add name="dhcp1" interface=' . $lan . ' address-pool="dhcp-pool" lease-time=1h disabled=no';
            $lines[] = '/ip dhcp-server lease add address=' . $lanIp . ' mac-address=02:BB:01:00:00:01 dynamic=no comment="IP Reservado - MyISP Server"';
            $lines[] = '';
            $lineNum++;
        }

        if ($scriptType === 'complete') {
            $lines[] = '# --------------------------------------------';
            $lines[] = '# ' . $lineNum . '. SERVIDOR RADIUS';
            $lines[] = '# --------------------------------------------';
            $lines[] = '/radius add address=127.0.0.1 secret=myisp-radius service=pppoe,hotspot authentication-port=1812 accounting-port=1813';

            $lines[] = '# API RouterOS (para o MyISP se conectar)';
            $lines[] = '/ip service set api disabled=no port=8728';
            $lines[] = '/ip service set api-ssl disabled=no';
            $lines[] = '/user add name=myisp password="' . $adminPass . '" group=full comment="Usuario API - MyISP"';
            $lines[] = '';
            $lineNum++;

            $lines[] = '# --------------------------------------------';
            $lines[] = '# ' . $lineNum . '. PERFIS PPPoE POR PLANO (BANDA)';
            $lines[] = '# --------------------------------------------';
            $lines[] = '# Perfis usados pelo provisionamento MyISP: plano-<slug>';
            $lines[] = '/ppp profile add name="plano-5m" local-address=' . $pppoe['gateway'] . ' remote-address="pool-pppoe" dns-server=' . $dns . ' rate-limit=' . $bandwidthDown . '/' . $bandwidthUp . ' comment="Plano 5Mbps"';
            $lines[] = '/ppp profile add name="plano-10m" local-address=' . $pppoe['gateway'] . ' remote-address="pool-pppoe" dns-server=' . $dns . ' rate-limit=10M/5M comment="Plano 10Mbps"';
            $lines[] = '/ppp profile add name="plano-20m" local-address=' . $pppoe['gateway'] . ' remote-address="pool-pppoe" dns-server=' . $dns . ' rate-limit=20M/10M comment="Plano 20Mbps"';
            $lines[] = '/ppp profile add name="plano-50m" local-address=' . $pppoe['gateway'] . ' remote-address="pool-pppoe" dns-server=' . $dns . ' rate-limit=50M/25M comment="Plano 50Mbps"';
            $lines[] = '';
            $lineNum++;

            $lines[] = '# --------------------------------------------';
            $lines[] = '# ' . $lineNum . '. AGENDA DE BLOQUEIO (MyISP)';
            $lines[] = '# --------------------------------------------';
            $lines[] = '# Agenda diaria para checagem via API do MyISP';
            $lines[] = '/system scheduler add name="myisp-check" interval=5m start-time=00:00:00 comment="Chamada de bloqueio/desbloqueio MyISP" on-event="/tool fetch url=\"http://' . $lanIp . ':8000/api/infra/blocked-addresses\" keep-result=no"';
            $lines[] = '';
            $lineNum++;

            $lines[] = '# --------------------------------------------';
            $lines[] = '# ' . $lineNum . '. BACKUP AUTOMATICO';
            $lines[] = '# --------------------------------------------';
            $lines[] = '/system scheduler add name="backup-diario" start-time=03:00:00 interval=1d on-event="/system backup save name=myisp-daily"';
            $lines[] = '/system scheduler add name="export-diario" start-time=03:05:00 interval=1d on-event="/export file=myisp-config"';
        }

        $lines[] = '';
        $lines[] = '# ============================================';
        $lines[] = '# FIM DA CONFIGURACAO MyISP';
        $lines[] = '# ============================================';
        $lines[] = '';
        $lines[] = '# Proximos passos:';
        $lines[] = '# 1. Acesse o WinBox e conecte na RB';
        $lines[] = '# 2. Va em New Terminal';
        $lines[] = '# 3. Cole todo o script e pressione Enter';
        $lines[] = '# 4. Verifique se nao houve erros';
        $lines[] = '# 5. Configure o MyISP com este servidor MikroTik';
        $lines[] = '# 6. Teste a conexao em Servidores MikroTik > Testar';

        return implode("\n", $lines);
    }

    private function subnetAddress(string $ip, string $mask): string
    {
        $mask = (int) $mask;
        $mask = ($mask > 32 || $mask < 0) ? 24 : $mask;
        $ipLong = ip2long($ip);

        if ($ipLong === false) {
            return '192.168.0.0/16';
        }

        $maskLong = $mask === 0 ? 0 : (0xFFFFFFFF << (32 - $mask));

        return long2ip($ipLong & $maskLong) . '/' . $mask;
    }

    private function pppoeSubnet(string $ip): array
    {
        $parts = array_map('intval', explode('.', $ip));

        if (count($parts) < 3) {
            $base = '10.99.0';
        } else {
            $third = $parts[2] + 1;
            if ($third > 254) {
                $third = 1;
            }
            $base = $parts[0] . '.' . $parts[1] . '.' . $third;
        }

        return [
            'gateway' => $base . '.1',
            'start' => $base . '.10',
            'end' => $base . '.254',
            'subnet' => $base . '.0/24',
        ];
    }
}
