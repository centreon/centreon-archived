import { atom } from 'jotai';

interface PollerData {
  centreon_central_ip?: string;
  linked_remote_master?: string;
  linked_remote_slaves?: Array<string>;
  open_broker_flow?: boolean;
  server_ip?: string;
  server_name?: string;
  server_type?: string;
  submitStatus?: boolean;
}
export const pollerAtom = atom<PollerData>({});

export const setWizardDerivedAtom = atom(null, (get, set, data: PollerData) => {
  set(pollerAtom, { ...get(pollerAtom), ...data });
});
