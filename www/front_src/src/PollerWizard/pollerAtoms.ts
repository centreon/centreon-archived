import { atom } from 'jotai';

export interface PollerData {
  centreon_central_ip?: string;
  linked_remote_master?: string;
  linked_remote_slaves?: Array<string>;
  open_broker_flow?: boolean;
  server_ip?: string;
  server_name?: string;
  server_type?: string;
  submitStatus?: boolean;
}

export interface RemoteServerData {
  centreon_central_ip?: string;
  centreon_folder?: string;
  db_password?: string;
  db_user?: string;
  linked_pollers?: Array<string>;
  no_check_certificate?: boolean;
  no_proxy?: boolean;
  server_ip?: string;
  server_name?: string;
  server_type?: string;
  submitStatus?: boolean;
  taskId?: number | string;
}

export const pollerAtom = atom<PollerData | null>(null);
export const setWizardDerivedAtom = atom(null, (get, set, data: PollerData) => {
  set(pollerAtom, { ...get(pollerAtom), ...data });
});

export const remoteServerAtom = atom<RemoteServerData | null>(null);
export const setRemoteServerWizardDerivedAtom = atom(
  null,
  (get, set, data: RemoteServerData) => {
    set(remoteServerAtom, { ...get(remoteServerAtom), ...data });
  },
);
