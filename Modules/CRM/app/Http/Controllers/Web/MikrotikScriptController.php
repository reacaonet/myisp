<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\MikrotikServer;

class MikrotikScriptController extends Controller
{
    public function index(Request $request)
    {
        $servers = MikrotikServer::where('is_active', true)->orderBy('name')->get();
        return view('crm::mikrotik.scripts', compact('servers'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:mikrotik_servers,id',
            'script_type' => 'required|in:pppoe,hotspot,firewall,dhcp,complete',
            'wan_interface' => 'nullable|string|max:50',
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

        return view('crm::mikrotik.script-result', compact('script', 'server', 'validated'));
    }

    private function buildScript(array $config, MikrotikServer $server): string
    {
        $wan = $config['wan_interface'] ?? 'ether1';
        $lanIp = $config['lan_ip'] ?? '192.168.1.1';
        $lanMask = $config['lan_mask'] ?? '24';
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
        $lines[] = '/ip address add address=' . $lanIp . '/' . $lanMask . ' interface=ether2 comment="LAN - MyISP"';
        $lines[] = '/ip dns set servers=' . $dns . ' allow-remote-requests=yes';
        $lines[] = '/ip route add dst-address=0.0.0.0/0 gateway=' . $wan . ' comment="Rota Default - WAN"';
        $lines[] = '/system ntp client set enabled=yes';
        $lines[] = '/system ntp client servers add address=' . $ntp;
        $lines[] = '/ip pool add name="' . $poolName . '" ranges=' . $poolStart . '-' . $poolEnd;
        $lines[] = '';

        $lineNum = $adminPass ? 4 : 3;

        if ($scriptType === 'pppoe' || $scriptType === 'complete') {
            $lines[] = '# --------------------------------------------';
            $lines[] = '# ' . $lineNum . '. SERVIDOR PPPoE';
            $lines[] = '# --------------------------------------------';
            $lines[] = '/interface pppoe-server server add service-name="' . $pppoeService . '" interface=ether2 mtu=' . $mtuLan . ' mru=' . $mtuLan . ' default-profile=default';
            $lines[] = '/ppp profile add name="pppoe-profile" local-address=' . $lanIp . ' remote-address="' . $poolName . '" dns-server=' . $dns . ' use-upnp=no';
            $lines[] = '/ppp aaa set use-radius=yes accounting=yes interim-update=5m';
            $lines[] = '';
            $lineNum++;
        }

        if ($scriptType === 'hotspot' || $scriptType === 'complete') {
            $lines[] = '# --------------------------------------------';
            $lines[] = '# ' . $lineNum . '. SERVIDOR HOTSPOT';
            $lines[] = '# --------------------------------------------';
            $lines[] = '/ip hotspot profile add name="hsprof1" hotspot-address=' . $lanIp . ' dns-name="hotspot.' . $lanIp . '.sslip.io" html-directory=hotspot';
            $lines[] = '/ip hotspot add name="' . $hotspotName . '" interface=ether2 profile="hsprof1" address-pool="' . $poolName . '" addresses="' . $lanIp . '/' . $lanMask . '"';
            $lines[] = '/ip hotspot user profile add name="hs-user-profile" idle-time=5m session-timeout=0s rate-limit="' . $bandwidthDown . '/' . $bandwidthUp . '"';
            $lines[] = '/ip hotspot user add name="admin" password="admin" profile="hs-user-profile" server="' . $hotspotName . '" comment="Usuario administrativo"';
            $lines[] = '';
            $lineNum++;
        }

        if ($scriptType === 'firewall' || $scriptType === 'complete') {
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
            $lines[] = '/ip firewall filter add chain=input dst-port=8291 protocol=tcp src-address=192.168.0.0/16 action=accept comment="Permitir WinBox LAN"';
            $lines[] = '/ip firewall filter add chain=input dst-port=8291 protocol=tcp action=drop comment="Bloquear WinBox WAN"';
            $lines[] = '/ip firewall filter add chain=input protocol=tcp dst-port=23 action=drop comment="Bloquear Telnet"';
            $lines[] = '/ip firewall filter add chain=input protocol=tcp dst-port=21 action=drop comment="Bloquear FTP"';
            $lines[] = '';
            $lines[] = '# Address List - Bloqueio de clientes inadimplentes (MyISP)';
            $lines[] = '/ip firewall address-list add list=myisp-blocked address=0.0.0.0 comment="Lista para bloqueio MyISP"';
            $lines[] = '/ip firewall filter add chain=forward src-address-list=myisp-blocked action=drop comment="Bloquear Inadimplentes - Upload"';
            $lines[] = '/ip firewall filter add chain=forward dst-address-list=myisp-blocked action=drop comment="Bloquear Inadimplentes - Download"';
            $lines[] = '';
            $lineNum++;
        }

        if ($scriptType === 'dhcp' || $scriptType === 'complete') {
            $lines[] = '# --------------------------------------------';
            $lines[] = '# ' . $lineNum . '. SERVIDOR DHCP';
            $lines[] = '# --------------------------------------------';
            $lines[] = '/ip pool add name="dhcp-pool" ranges=' . $poolStart . '-' . $poolEnd;
            $lines[] = '/ip dhcp-server network add address=' . $lanIp . '/' . $lanMask . ' dns-server=' . $dns . ' gateway=' . $lanIp;
            $lines[] = '/ip dhcp-server add name="dhcp1" interface=ether2 address-pool="dhcp-pool" lease-time=1h disabled=no';
            $lines[] = '/ip dhcp-server lease add address=' . $lanIp . ' mac-address=* * dynamic=no comment="IP Reservado - MyISP Server"';
            $lines[] = '';
            $lineNum++;
        }

        if ($scriptType === 'complete') {
            $lines[] = '# --------------------------------------------';
            $lines[] = '# ' . $lineNum . '. SERVIDOR RADIUS';
            $lines[] = '# --------------------------------------------';
            $lines[] = '/radius add address=127.0.0.1 secret=myisp-radius service=pppoe,hotspot authentication-port=1812 accounting-port=1813';
            $lines[] = '/ip service set www-ssl disabled=no tls-version=only tls-cipher=ecdsa';
            $lines[] = '/ip service set api disabled=no';
            $lines[] = '/ip service set api-ssl disabled=no';
            $lines[] = '';
            $lineNum++;

            $lines[] = '# --------------------------------------------';
            $lines[] = '# ' . $lineNum . '. QUEUE SIMPLE (BANDA POR PLANO)';
            $lines[] = '# --------------------------------------------';
            $lines[] = '/queue simple add name="plano-5m" target="' . $lanIp . '/' . $lanMask . '" max-limit=' . $bandwidthDown . '/' . $bandwidthUp . ' comment="Plano 5Mbps"';
            $lines[] = '/queue simple add name="plano-10m" target="' . $lanIp . '/' . $lanMask . '" max-limit=10M/5M burst-limit=12M/6M burst-threshold=8M/4M burst-time=10s comment="Plano 10Mbps"';
            $lines[] = '/queue simple add name="plano-20m" target="' . $lanIp . '/' . $lanMask . '" max-limit=20M/10M burst-limit=24M/12M burst-threshold=16M/8M burst-time=10s comment="Plano 20Mbps"';
            $lines[] = '/queue simple add name="plano-50m" target="' . $lanIp . '/' . $lanMask . '" max-limit=50M/25M burst-limit=60M/30M burst-threshold=40M/20M burst-time=10s comment="Plano 50Mbps"';
            $lines[] = '';
            $lineNum++;

            $lines[] = '# --------------------------------------------';
            $lines[] = '# ' . $lineNum . '. AGENDA DE BLOQUEIO (MyISP)';
            $lines[] = '# --------------------------------------------';
            $lines[] = '/system scheduler add name="myisp-check" interval=5m start-time=00:00:00 on-event=';
            $lines[] = '# Este scheduler deve ser configurado para chamar a API do MyISP';
            $lines[] = '# e bloquear/desbloquear clientes via address-list';
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
}
