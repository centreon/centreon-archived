export interface License {
  expiration_date: string;
  required: true;
}

export interface LicenseProps {
  color: string;
  label: string;
}

export interface Version {
  available: string;
  current: string;
  installed: boolean;
  outdated: boolean;
}

export interface Entity {
  description: string;
  id: string;
  images: Array<string>;
  label: string;
  last_update: string;
  license: License;
  release_note: string;
  stability: string;
  title: string;
  type: string;
  version: Version;
}

export interface Extensions {
  module: {
    entities: Array<Entity>;
  };
  widget: {
    entities: Array<Entity>;
  };
}

export interface ExtensionResult {
  result: Extensions | string;
  status: boolean;
}

export interface InstallOrUpdateExtensionResult {
  result: {
    entity: Entity | null;
    message: string;
  };
  status: boolean;
}

export interface ExtensionsStatus {
  [id: string]: boolean;
}

export interface EntityType {
  id: string;
  type: string;
}

export interface EntityDeleting {
  description: string;
  id: string;
  type: string;
}

export interface ExtensionDetails {
  result: Entity;
  status: boolean;
}
