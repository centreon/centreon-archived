export interface PlatformInstallationStatus {
  hasUpgradeAvailable: boolean;
  isInstalled: boolean;
}

interface Version {
  version: string;
}

export interface PlatformVersions {
  modules: Record<string, Version>;
  web: Version;
}
