import { Tabs } from 'expo-router';
import { Platform } from 'react-native';
import { Icon, IconName } from '@/components';
import { colors, spacing } from '@/theme';

type TabConfig = {
  name: string;
  title: string;
  icon: IconName;
};

const tabs: TabConfig[] = [
  { name: 'feed', title: 'الرئيسية', icon: 'home' },
  { name: 'search', title: 'استكشف', icon: 'search' },
  { name: 'notifications', title: 'إشعارات', icon: 'bell' },
  { name: 'profile', title: 'حسابي', icon: 'user' },
];

export default function TabsLayout() {
  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: colors.coral[500],
        tabBarInactiveTintColor: colors.ink[400],
        tabBarLabelStyle: {
          fontSize: 11,
          fontWeight: '800',
          marginTop: 2,
        },
        tabBarStyle: {
          backgroundColor: colors.white,
          borderTopColor: 'rgba(11,11,12,0.06)',
          paddingTop: spacing[1],
          height: Platform.OS === 'ios' ? 86 : 64,
        },
      }}
    >
      {tabs.map((t) => (
        <Tabs.Screen
          key={t.name}
          name={t.name}
          options={{
            title: t.title,
            tabBarIcon: ({ color, focused }) => (
              <Icon name={t.icon} size={focused ? 24 : 22} color={color} />
            ),
          }}
        />
      ))}
    </Tabs>
  );
}
