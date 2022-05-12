import { lazy, Suspense, useEffect, useMemo, useState } from 'react';

import { atom, useAtom } from 'jotai';
import { isEmpty } from 'ramda';

import { MenuSkeleton, PageSkeleton } from '@centreon/ui';

import NotFoundPage from '../../FallbackPages/NotFoundPage';
import memoizeComponent from '../../Resources/memoizedComponent';

import loadComponent from './loadComponent';

interface UseDynamicLoadRemoteEntryState {
  failed: boolean;
  ready: boolean;
}

interface UseDynamicLoadRemoteEntryProps {
  moduleName: string;
  remoteEntry: string;
}

const remoteEntriesLoadedAtom = atom([] as Array<string>);

const useDynamicLoadRemoteEntry = ({
  remoteEntry,
  moduleName,
}: UseDynamicLoadRemoteEntryProps): UseDynamicLoadRemoteEntryState => {
  const [failed, setFailed] = useState(false);

  const [remoteEntriesLoaded, setRemoteEntriesLoaded] = useAtom(
    remoteEntriesLoadedAtom,
  );

  useEffect((): (() => void) | undefined => {
    if (isEmpty(remoteEntry)) {
      return undefined;
    }

    const remoteEntryElement = document.getElementById(moduleName);

    if (remoteEntryElement && remoteEntriesLoaded.includes(moduleName)) {
      return undefined;
    }

    const element = document.createElement('script');
    element.src = `./modules/${moduleName}/static/${remoteEntry}`;
    element.type = 'text/javascript';
    element.id = moduleName;

    element.onload = (): void => {
      setRemoteEntriesLoaded((currentRemoteEntries) => [
        ...currentRemoteEntries,
        moduleName,
      ]);
    };

    element.onerror = (): void => {
      setFailed(true);
    };

    document.head.appendChild(element);

    return (): void => {
      document.head.removeChild(element);
    };
  }, []);

  return {
    failed,
    ready: remoteEntriesLoaded.includes(moduleName),
  };
};

interface LoadComponentProps {
  component: string;
  isFederatedModule?: boolean;
  moduleFederationName: string;
}

const LoadComponent = ({
  moduleFederationName,
  component,
  isFederatedModule,
  ...props
}: LoadComponentProps): JSX.Element => {
  const Component = useMemo(
    () => lazy(loadComponent({ component, moduleFederationName })),
    [moduleFederationName],
  );

  return (
    <Suspense
      fallback={
        isFederatedModule ? <MenuSkeleton width={29} /> : <PageSkeleton />
      }
    >
      <Component {...props} />
    </Suspense>
  );
};

const MemoizedLoadComponent = memoizeComponent<LoadComponentProps>({
  Component: LoadComponent,
  memoProps: ['name', 'component', 'isFederatedModule'],
});

interface RemoteProps extends LoadComponentProps {
  moduleName: string;
  remoteEntry: string;
}

export const Remote = ({
  component,
  remoteEntry,
  moduleName,
  moduleFederationName,
  isFederatedModule,
  ...props
}: RemoteProps): JSX.Element => {
  const { ready, failed } = useDynamicLoadRemoteEntry({
    moduleName,
    remoteEntry,
  });

  if (!ready) {
    return isFederatedModule ? <MenuSkeleton width={29} /> : <PageSkeleton />;
  }

  if (failed) {
    return <NotFoundPage />;
  }

  return (
    <MemoizedLoadComponent
      component={component}
      isFederatedModule={isFederatedModule}
      moduleFederationName={moduleFederationName}
      {...props}
    />
  );
};
