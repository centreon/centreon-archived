import { lazy, Suspense, useEffect, useMemo, useState } from 'react';

import { MenuSkeleton } from '@centreon/ui';

import loadComponent from './loadComponent';

interface UseDynamicLoadRemoteEntryState {
  failed: boolean;
  ready: boolean;
}

interface UseDynamicLoadRemoteEntryProps {
  moduleName: string;
  remoteEntry: string;
}

const useDynamicLoadRemoteEntry = ({
  remoteEntry,
  moduleName,
}: UseDynamicLoadRemoteEntryProps): UseDynamicLoadRemoteEntryState => {
  const [ready, setReady] = useState(false);
  const [failed, setFailed] = useState(false);

  useEffect((): (() => void) | undefined => {
    if (!remoteEntry) {
      return undefined;
    }
    console.log(`Dynamic Script Loading: ${remoteEntry}`);

    const element = document.createElement('script');
    element.src = `./modules/${moduleName}/static/${remoteEntry}`;
    element.type = 'text/javascript';
    element.async = true;

    setReady(false);
    setFailed(false);

    element.onload = (): void => {
      console.log(`Dynamic Script Loaded: ${remoteEntry}`);
      setReady(true);
    };

    element.onerror = (): void => {
      console.error(`Dynamic Script Error: ${remoteEntry}`);
      setReady(false);
      setFailed(true);
    };

    document.head.appendChild(element);

    return () => {
      console.log(`Dynamic Script Removed: ${remoteEntry}`);
      document.head.removeChild(element);
    };
  }, [remoteEntry]);

  return {
    failed,
    ready,
  };
};

interface LoadComponentProps {
  component: string;
  isHook?: boolean;
  name: string;
}

const LoadComponent = ({
  name,
  component,
  isHook,
  ...props
}: LoadComponentProps): JSX.Element => {
  console.log(name, component);
  const Component = useMemo(
    () => lazy(loadComponent({ component, moduleName: name })),
    [name],
  );

  return (
    <Suspense
      fallback={isHook ? <MenuSkeleton width={29} /> : `Loading remote ${name}`}
    >
      <Component {...props} />
    </Suspense>
  );
};

interface RemoteProps extends LoadComponentProps {
  moduleName: string;
  remoteEntry: string;
}

export const Remote = ({
  component,
  remoteEntry,
  moduleName,
  name,
  isHook,
  ...props
}: RemoteProps): JSX.Element => {
  const { ready, failed } = useDynamicLoadRemoteEntry({
    moduleName,
    remoteEntry,
  });

  if (!ready) {
    return <h2>Loading dynamic script: {remoteEntry}</h2>;
  }

  if (failed) {
    return <h2>Failed to load dynamic script: {remoteEntry}</h2>;
  }

  return (
    <LoadComponent
      component={component}
      isHook={isHook}
      name={name}
      {...props}
    />
  );
};
